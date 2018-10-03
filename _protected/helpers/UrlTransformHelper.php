<?php
namespace app\helpers;

use Yii;
use yii\helpers\Url;
use yii\helpers\StringHelper;

/**
 * helper to create short version url report 
 * UrlTransform 1.0
 * 
 * @package     MyBase
 * @author      Jonathan Hilgeman
 * @modified    2018-08-04
 *
*/

class UrlTransformHelper
{

	// public $Kd_Laporan;
    // public $Kd_Sumber;
    // public $Kd_Bidang;
    // public $Kd_Unit;
    // public $Kd_Sub;
    // public $Tgl_1;
    // public $Tgl_2;
    // public $Tgl_Laporan;
    // public $perubahan_id;
    // public $unit_id;
    // public $kategori_unit_id;
	// public $unit_id;

	/**
	 * $url can be set manually
	 * just override url value with your certain url
	 */
	public $url = null;

	protected function getUrl()
	{
		return $this->setUrl();
	}

	public function setUrl($url = null)
	{
		// $this->Url = Yii::$app->request;
		
		// // if($url) $this->url = $url;

		return Yii::$app->request;
	}

	protected function setPathInfo()
	{
		$pathInfo = $this->getUrl()->pathInfo;
		$kodePathInfo = 0;
		if(StringHelper::startsWith($pathInfo, 'pelaporan/pelaporanpuskesmas') || StringHelper::startsWith($pathInfo, 'pelaporan/pelaporanpantau')){
			$kodePathInfo = 1; //  jika pelaporan sekolah maka gunakan kode 1, nanti akan menyesuaikan dengan keberadaan unit_id pada user yang sedang login
		}

		return $kodePathInfo;
	}

	protected function getQueryParams()
	{
		return $this->getUrl()->queryParams;
	}

	private function shortenParamVocabulary()
	{
		return [
			'Kd_Laporan' => 'kode2',
			'Kd_Sumber' => 'kode3',
			'Tgl_1' => 'kode4',
			'Tgl_2' => 'kode5',
			'Tgl_Laporan' => 'kode6',
			'perubahan_id' => 'kode7',
			'jenis_unit_id' => 'kode8',
			'kategori_unit_id' => 'kode9',
			'unit_id' => 'kode10'
		];
	}
	
	private function expandParamVocabulary()
	{
		return [
			2 => 'Kd_Laporan',
			3 => 'Kd_Sumber',
			4 => 'Tgl_1',
			5 => 'Tgl_2',
			6 => 'Tgl_Laporan',
			7 => 'perubahan_id',
			8 => 'unit_id',
			9 => 'kategori_unit_id',
			10 => 'unit_id'
		];
	}

	private function setShortenParam($param)
	{
		
	}


	/**
	 * menghasilkan url yang telah diperpendek
	 * @param kode1 adalah pathInfo
	 * @param kode2 adalah Kd_Laporan
	 * @param kode3 adalah Kd_Sumber
	 * @param kode4 adalah Tgl_1 yang diubah ke integer
	 * @param kode5 adalah Tgl_2 yang diubah ke integer
	 * @param kode6 adalah Tgl_Laporan yang diubah ke integer
	 * @param kode7 adalah perubahan_id
	 * @param kode8 adalah unit_id
	 * @param kode9 adalah kategori_unit_id
	 * @param kode10 adalah unit_id
	 * jika salah satu param tidak ada maka diisi dnegan n
	 */
	public function shorten()
	{
		$kode1 = $kode2 = $kode3 = $kode4 = $kode5 = $kode6 = $kode7 = $kode8 = $kode9 = $kode10 = 'n';

		if($this->setPathInfo()) $kode1 = $this->setPathInfo();

		foreach ($this->getQueryParams()['Laporan'] as $key => $value) {
			if(!$this->shortenParamVocabulary()[$key]) return false;
			$paramName = $this->shortenParamVocabulary()[$key];
			${$paramName} = $value;
			if($paramName == 'kode4' || $paramName == 'kode5' || $paramName == 'kode6')
			{
				// ${$paramName} = strtotime($value);
				${$paramName} = str_replace('-', '' , $value);
			}
		}

		if(isset(Yii::$app->user->identity->unit_id)) $kode10 = Yii::$app->user->identity->unit_id;

		return Url::to(['/ex', 'id' => $kode1.'-'.$kode2.'-'.$kode3.'-'.$kode4.'-'.$kode5.'-'.$kode6.'-'.$kode7.'-'.$kode8.'-'.$kode9.'-'.$kode10], true);
	}

	public function expand($url)
	{

		$params = explode('-', $url);
		if($params[0] == 1){
			$action = 'pelaporan/pelaporanpantau/cetak';
			if(isset(Yii::$app->user->identity->unit_id)) $action = 'pelaporan/pelaporanpuskesmas/cetak';
			$model = 'Laporan';
		}
		$paramVocabs = $this->expandParamVocabulary();	
		$getParam = [];

		foreach ($params as $key => $value) {
			$numberOrder = $key+1;
			if($numberOrder == 1){
				// do nothing
			}elseif($numberOrder == 4 || $numberOrder == 5 || $numberOrder == 6) {
				$dateValue = substr($value, 0, 4)."-".substr($value, 5, 2)."-".substr($value, -2);
				$getParam[$paramVocabs[$numberOrder]] = $dateValue;
			}else{
				$getParam[$paramVocabs[$numberOrder]] = $value;
			}
		}

		// return $getParam;

		return Url::to([$action, $model => $getParam], true);

		return false;
	}
}
?>