<?php
namespace KVRLP\KvrlpSecuredownloads\Utility;

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
 * utility to calculate and check hashes for get parameters
 *
 * @author Alexander Wende <alexander.wende@kv-rlp.de>
 */

class HashCalculator{

    /**
     * Array of attributes
     */
    protected $attributes;

    /**
     * set attribute
     * @param $name
     * @param $value
     * @return hashCalculator
     */
    public function setAttribute($name,$value){
        $this->attributes[$name] = (string) $value;
        return $this;
    }

    /**
     * remove attribute
     * @param $name
     * @return hashCalculator
     */
    public function unsetAttribute($name){
        unset($this->attributes[$name]);
        return $this;
    }

    /**
     * calculate and returns the hash
     * @return string Hashvalue
     */
    public function getHash(){
        $this->setAttribute('encryptionKey',$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
        ksort($this->attributes);
        return  md5(serialize($this->attributes));
    }

    /**
     * check if hash mathes the given attributes
     * @param $hash
     * @return bool
     */
    public function checkHash($hash){
        return ($hash === $this->getHash());
    }

}