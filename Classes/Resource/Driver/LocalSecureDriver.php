<?php
namespace KVRLP\KvrlpSecuredownloads\Resource\Driver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

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
 * fal driver for secure downloads
 *
 * @author Alexander Wende <alexander.wende@kv-rlp.de>
 */

class LocalSecureDriver extends \TYPO3\CMS\Core\Resource\Driver\LocalDriver{
    /**
     * Calculates the absolute path to this drivers storage location.
     *
     * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException
     * @param array $configuration
     * @return string
     */
    protected function calculateBasePath(array $configuration) {
        if (!array_key_exists('basePath', $configuration) || empty($configuration['basePath'])) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException('Configuration must contain base path.', 1346510477);
        }

        if ($configuration['pathType'] === 'relative') {
            $relativeBasePath = $configuration['basePath'];
            $absoluteBasePath = PATH_site . $relativeBasePath;
        } else {
            $absoluteBasePath = $configuration['basePath'];
        }
        $absoluteBasePath = rtrim($absoluteBasePath, '/') . '/';
        if (!is_dir($absoluteBasePath)) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException('Base path "' . $absoluteBasePath . '" does not exist or is no directory.', 1299233097);
        }
        return $absoluteBasePath;
    }


    /**
     * Determines the base URL for this driver, from the configuration or
     * the TypoScript frontend object
     *
     * @return void
     */
    protected function determineBaseUrl() {

        if (@\TYPO3\CMS\Core\Utility\GeneralUtility::isValidUrl($GLOBALS['TSFE']->config['config']['baseURL'])) {
                $this->baseUri = @rtrim($GLOBALS['TSFE']->config['config']['baseURL'], '/') . '/';
        } else {
            $this->baseUri = '\\';
            }
    }
    /**
     * Returns the public URL to a file. For the local driver, this will always
     * return a path relative to PATH_site.
     *
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface  $fileOrFolder
     * @param bool $relativeToCurrentScript Determines whether the URL returned should be relative to the current script, in case it is relative at all (only for the LocalDriver)
     * @return string
     */
    public function getPublicUrl(\TYPO3\CMS\Core\Resource\ResourceInterface $fileOrFolder, $relativeToCurrentScript = FALSE) {

        $additionalUri = '';
        $additionalParameters = array(
            'id' => ($GLOBALS['TSFE']->id > 0) ? $GLOBALS['TSFE']->id : 0,
            'identifier'=>$fileOrFolder->getIdentifier(),
            'eID' => 'kvrlpSecureDownloads',
            'sid'=>$this->storage->getUid()
        );
        
        $hashCalculator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('KVRLP\\KvrlpSecuredownloads\\Utility\\HashCalculator');
        foreach($additionalParameters as $parameterName => $parameterValue){
            $hashCalculator->setAttribute($parameterName,$parameterValue);
            $additionalUri .= '&'.urlencode($parameterName).'='.urlencode($parameterValue);
        }
        $additionalUri.='&h='.$hashCalculator->getHash();

        $publicUrl = rtrim($this->baseUri,'/') .'/index.php?'. ltrim($additionalUri,'&');
        
        // If requested, make the path relative to the current script in order to make it possible
        // to use the relative file
        if ($relativeToCurrentScript) {
                $publicUrl = PathUtility::getRelativePathTo(PathUtility::dirname((PATH_site . $publicUrl))) . PathUtility::basename($publicUrl);
        }
        return $publicUrl;
    }

}
