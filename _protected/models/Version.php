<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * by Heru Arief Wijaya
 * This is model for version Number
 * All version release will be set here
 * @param integer $thisReleaseNumber this param use to set this code release number
 * @param integer $__releaseNumber this param use to call any release number other than $thisReleaseNumber.
 * 
 * Example use if you want to get current release
 * $version = new Version();
 * versionNumber: $version->releaseNumber; // output 5
 * versionNumber: $version->getReleaseNumber; // output 5
 * versionNumber: $version->thisReleaseNumber; // output 5
 * versionName: $version->versionName; // output v1.3.0
 * versionName: $version->getVersionName(); // output v1.3.0
 * Example use if you want to get certain release
 * $version = new Version();
 * $version->__releaseNumber = 1;
 * versionNumber: $version->releaseNumber; // output 1
 * versionNumber: $version->getReleaseNumber; // output 1
 * versionNumber: $version->thisReleaseNumber; // output 1
 * versionName: $version->versionName; // output v1.2.3
 * versionName: $version->getVersionName(); // output v1.2.3
 */
class Version extends Model
{
    public $__releaseNumber = null;
    public $thisReleaseNumber = 1;

    public function versions()
    {
        return  [
            1 => [
                'name' => 'v1.0.0',
                'notes' => '
                    Final Release of Ditta v1.0.0 ready for production (Sprint 1)
                ',
                'date' => strtotime('2018-09-01'),
            ],
        ];        
    }

    public function setRelease($thisReleaseNumber)
    {
        if($this->__releaseNumber !== null) $thisReleaseNumber = $this->__releaseNumber;
        return $thisReleaseNumber;
    }

    public function getReleaseNumber()
    {
        return $this->setRelease($this->thisReleaseNumber);
    }

    public function getVersionName()
    {
        return isset($this->versions()[$this->releaseNumber]) ? $this->versions()[$this->releaseNumber]['name'] : 'Invalid Version';
    }
}
