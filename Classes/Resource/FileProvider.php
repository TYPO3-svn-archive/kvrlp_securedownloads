<?php
namespace KVRLP\KvrlpSecuredownloads\Resource;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alexander Wende <alexander.wende@kv-rlp.de>, 
 *           Kassenärztliche Vereinigung Rheinland-Pfalz
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * checks rights and provide the file
 * this file provider based on naw_securedl by Dietrich Heise and Helmut Hummel
 *
 * @author Alexander Wende <alexander.wende@kv-rlp.de>
 */


class FileProvider{
    
    /**
     * @var \TYPO3\CMS\Core\Resource\File
     */
    protected $file;
    
    /**
     * @var TYPO3\CMS\Frontend\Authentication\FrontendUserAuthtenication
     */
    protected $frontendUserAuthtenication;
    
    /**
     * @var string
     */
    protected $identifier;
    
    /**
     * @var array with Page Rows
     */
    protected $page;

    /**
     * @var string
     */
    protected $requestHash;

    /**
     * @var TYPO3\CMS\Core\Resource\ResourceStorage
     */
    protected $resourceStorage;
    
    /**
     * @var TYPO3\CMS\Core\Resource\StorageRepository
     * @inject
     */
    protected $storageRepository;
    

    public function __construct(){
        $this->frontendUserAuthtenication = $GLOBALS['TSFE']->fe_user;
        $this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\StorageRepository');
    }
    
    /**
     * setter for file
     * @param string $identifier
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\NotFoundException
     * @throws \Exception
     * @return FileProvider
     */
    protected function setFileByIdentifier($identifier){
        try{
            $this->file = $this->resourceStorage->getFile($this->identifier);
        }catch(\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException $fileNotFoundException){
            throw new \KVRLP\KvrlpSecuredownloads\Exception\NotFoundException('Datei existiert nicht');
        }catch(\Exception $getFileException){
            throw new \Exception('Datei konnte nicht geholt werden');
        }
        return $this;
    }
    
    /**
     * setter for die file identifier
     * @param string $identifier
     * @return \KVRLP\KvrlpSecuredownloads\Resource\FileProvider
     */
    public function setIdentifier($identifier){
        $this->identifier = $identifier;
        return $this;
    }
    
    /**
     * setter for the uid of the page which fe access rights should be used
     * @param integer $id
     * @return \KVRLP\KvrlpSecuredownloads\Resource\FileProvider
     */
    public function setPageById($id){
        $pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $pageRepository->init(false);
        $this->page = $pageRepository->getPage($id);

        return $this;
    }

    /**
     * setter for the hash of get parameters
     * @param type $hash
     * @return \KVRLP\KvrlpSecuredownloads\Resource\FileProvider
     */
    public function setRequestHash($hash){
        $this->requestHash = $hash;
        return $this;
    }

    /**
     * setter for the storage uid
     * @param integer $storageId
     * @return FileProvider
     */
    public function setStorageById($storageId){
        $this->resourceStorage = $this->storageRepository->findByUid(($storageId));
        return $this;
    }
    
    /**
     * check if the user a access to the file
     * throws exception if no access
     * @return FileProvider
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\NoPageAccessException
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\NoFileAccessException
     */
    protected function checkAccess(){
        if(!$this->permitByIp()){
           if (!$this->hasUserPageAccess()){
               throw new \KVRLP\KvrlpSecuredownloads\Exception\NoPageAccessException('Kein Zugriff auf die Seite');
           }
           if (!$this->hasUserStorageAccess()){
                throw new \KVRLP\KvrlpSecuredownloads\Exception\NoFileAccessException('Keine Berechtigung für diesen Download');
           }
           if (!$this->hasUserFileAccess()){
                throw new \KVRLP\KvrlpSecuredownloads\Exception\NoFileAccessException('Keine Berechtigung für diesen Download');
           }
        }
        return $this;
    }
    
    /**
     * checks the given hash
     * @return boolean
     */
    protected function checkHash(){
        if (!$this->requestHash || count($this->page) == 0)
            return false;
        $hashCalculator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('KVRLP\KvrlpSecuredownloads\Utility\\HashCalculator');
        $hashCalculator->setAttribute('eID','kvrlpSecureDownloads')->setAttribute('sid',$this->resourceStorage->getUid())->setAttribute('identifier',$this->identifier)->setAttribute('id',$this->page['uid']);
        return $hashCalculator->checkHash($this->requestHash);
    }
    
    /**
     * checks if the frontendunser has access to the file
     * @return bool false if no access to file
     * @throws Exception
     */
    protected function hasUserFileAccess(){
        if (!$this->file instanceof \TYPO3\CMS\Core\Resource\File){
            throw new \Exception('Could not fetch file');
        }
        return $this->isFeUserMemberOf($this->file->getProperty('frontend_user_group'));
    }
    
    /**
     * checks if the user has access to the current page
     * @return bool false if no access
     */
    protected function hasUserPageAccess(){
        if (empty($this->page['fe_group'])){
            return true; //no fe_group set. Access for all
        }

        $allowedFeGroupIds = explode(',',$this->page['fe_group']);
        foreach($allowedFeGroupIds as $allowedFeGroupId){
            if (in_array($allowedFeGroupId,$this->frontendUserAuthtenication->groupData['uid'])){
                return true;
            }
        }
    }
    
    /**
     * checks if the frontenduser has access to the storage
     * @return bool no access to storage
     * @throws Exception
     */
    protected function hasUserStorageAccess(){
        if (!$this->resourceStorage instanceof \TYPO3\CMS\Core\Resource\ResourceStorage){
            throw new \Exception('Could not fetch Resource Storage');
        }
        $storageConfiguration = $this->resourceStorage->getConfiguration();
        return $this->isFeUserMemberOf($storageConfiguration['limitGroup']);
    }
    
    /**
     * Checks if the current fe user is member of one of the 
     * given Groups
     * @param string $feGroupUids
     * @return bool true if feGroupsUis is empty or feUser is member of a given group
     */
    protected function isFeUserMemberOf($feGroupUids = ''){
        //if no feGroupUids are given, user has always access
        $memberOfGroups = !empty($feGroupUids) ? explode(',',$feGroupUids) : '';
        if (!$memberOfGroups){
            return true; 
        }

        //if feGroupsUids are given and user is not logged in, user has never access
        if (empty($this->frontendUserAuthtenication->groupData['uid'])){
            return false; //Fe User is in none fe group. -> No Member
        }
        
        //checks if feUser is in any of the given feUserGroups
        foreach($memberOfGroups as $feGroupUid){
            if (in_array($feGroupUid,$this->frontendUserAuthtenication->groupData['uid'])){
                return true;
            }
        }
    }
    
    /**
     * checks if the remode address is permit to download every file from the storage
     * address can be changed in storage configuration
     * @return bool
     * @throws Exception
     */
    protected function permitByIp(){
        if (!$this->resourceStorage instanceof \TYPO3\CMS\Core\Resource\ResourceStorage){
            throw new Exception('Could not fetch Resource Storage');
        }
        $storageConfiguration = $this->resourceStorage->getConfiguration();
        foreach(explode(',',$storageConfiguration['allowedIps']) as $allowedIp){
            if (\t3lib_div::getIndpEnv('REMOTE_ADDR') === trim($allowedIp)){
                return true;
            }
        }
    }
    
    /**
     * provides the file
     * 
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\HashFailedException
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\NoPageAccessException
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\NoFileAccessException
     * @throws \KVRLP\KvrlpSecuredownloads\Exception\NotFoundException
     * @throws \Exception
     * @return void
     */
    public function provideFile(){
       if (!$this->checkHash())
           throw new \KVRLP\KvrlpSecuredownloads\Exception\HashFailedException('Attribute manipuliert');
       
       $this->setFileByIdentifier($this->identifier)
            ->checkAccess()
            ->sendResponseHeader()
            ->sendFilecontent();
    }
    
    /**
     * Sends the content of the file
     * @return \KVRLP\KvrlpSecuredownloads\Resource\FileProvider
     */
    protected function sendFilecontent(){
        $storageConfiguration = $this->resourceStorage->getConfiguration();
        $outputFilePath=$storageConfiguration['basePath'].$this->file->getIdentifier();
        readfile($outputFilePath);
        ob_flush();
        flush();
        return $this;
    }
    
    /**
     * Sets the repsonse header
     * @return FileProvider
     */
    protected function sendResponseHeader(){
        $outputFileName=basename($this->file->getIdentifier());
        $outputFileMimeType = new \finfo(FILEINFO_MIME, $outputFilePath);
       
        //set header
        header('Pragma: private');
        header('Expires: 0'); // set expiration time
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        if ($storageConfiguration['forceDownload'] === "1" || !strlen($outputFileMimeType) > 0){
            header('Content-Type: application/octet-stream');
        }else{
            header('Content-Type: ' . $outputFileMimeType);
        }
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $this->file->getSize());
        header('Content-Disposition: attachment; filename="' . $outputFileName . '"');
        return $this;
    }
}