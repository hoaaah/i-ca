<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TaValidasiPembayaranSearch represents the model behind the search form about `app\models\TaValidasiPembayaran`.
 */
class TruncateSchema extends Model
{
    public $truncateLevel;
    public $adminUsername;
    public $adminPassword;

    public function rules()
    {
        return [
            [['adminUsername', 'adminPassword', 'truncateLevel'], 'required'],
            ['adminPassword', 'validatePassword'],
            [['truncateLevel'], 'integer'],
            [['adminUsername', 'adminPassword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'truncateLevel' => 'Tingkat Penghapusan',
            'adminUsername' => 'Username Administrator',
            'adminPassword' => 'Password Administrator',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Helper method responsible for finding user based on the model scenario.
     * In Login With Email 'lwe' scenario we find user by email, otherwise by username
     * 
     * @return object The found User object.
     */
    private function findUser()
    {
        return User::findByUsername($this->adminUsername);
    }

    /**
     * Method that is returning User object.
     *
     * @return User|null
     */
    public function getUser()
    {
        return $this->findUser();
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute The attribute currently being validated.
     * @param array  $params    The additional name-value pairs.
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) {
            return false;
        }

        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->adminPassword)) {
            $field = 'Admininstrator Username' ;

            $this->addError($attribute, 'Incorrect '.$field.' or password.');
        }
    }

    public function truncateData()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        if($user->kd_user != 1){
            $this->addError('adminUsername', 'Hanya user dengan tingkat administrator yang dapat menghapus data.');
            return false;
        }

        switch ($this->truncateLevel) {
            case 1:
                if(!$this->truncatePenatausahaan()) return false;
                break;
            case 2:
                if(!$this->truncatePenatausahaan()) return false;
                if(!$this->truncatePenganggaran()) return false;
                break;
            case 3:
                if(!$this->truncatePenatausahaan()) return false;
                if(!$this->truncatePenganggaran()) return false;
                if(!$this->truncateParameter()) return false;
                break;
            case 4:
                if(!$this->truncateVerifikasiAsetTetap()) return false;
                break;
            case 5:
                if(!$this->truncateVerifikasiAsetTetap()) return false;
                if(!$this->truncateAsetTetap()) return false;
                break;
            default:
                # code...
                break;
        }

        $this->addError('adminUsername', 'Penghapusan '.$this->truncateLevelArray()[$this->truncateLevel].' berhasil!');

        return true;
    }

    public function truncateLevelArray(){
        return [
            1 => 'Penatausahaan',
            2 => 'Penganggaran',
            3 => 'Parameter',
            4 => 'Data Verifikasi Aset Tetap',
            5 => 'Data Aset Tetap'
        ];
    }

    private function truncatePenatausahaan()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $executeSp3b = Yii::$app->db->createCommand("
                DELETE FROM ta_sp2b;
                DELETE FROM ta_sp3b_rinc;
                DELETE FROM ta_sp3b;
            ")->execute();
            $executeSpj = Yii::$app->db->createCommand("
                DELETE FROM ta_spj_pot;
                DELETE FROM ta_spj_rinc;
                DELETE FROM ta_spj;
            ")->execute();
            $executePotongan = Yii::$app->db->createCommand("
                DELETE FROM ta_setoran_potongan_rinc;
                DELETE FROM ta_setoran_potongan;
            ")->execute();  
            $executeSaldoAwal = Yii::$app->db->createCommand("
                DELETE FROM ta_saldo_awal_potongan;
                DELETE FROM ta_saldo_awal;
            ")->execute();
            $executeMutasiKoreksi = Yii::$app->db->createCommand("
                DELETE FROM ta_mutasi_kas;
                DELETE FROM ta_koreksi;
            ")->execute();
            $transaction->commit();
            $alertColor = "info";
            $result = "Penghapusan Berhasil";
        } catch (\Exception $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal: ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        } catch (\Throwable $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        }
        return true;
    }

    private function truncatePenganggaran()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $executeSaldoAwal = Yii::$app->db->createCommand("
                DELETE FROM ta_saldo_awal_potongan;
                DELETE FROM ta_saldo_awal;
            ")->execute();
            $executeHistory = Yii::$app->db->createCommand("
                DELETE FROM ta_rkas_history;
                DELETE FROM ta_rkas_kegiatan_history;
                DELETE FROM ta_rkas_pendapatan_rencana_history;
                DELETE FROM ta_rkas_belanja_rencana_history;
                DELETE FROM ta_rkas_peraturan;
            ")->execute();
            $executeRkasPendapatan = Yii::$app->db->createCommand("
                DELETE FROM ta_rkas_pendapatan_rinc;
                DELETE FROM ta_rkas_pendapatan_rencana;
                DELETE FROM ta_rkas_pendapatan;
            ")->execute();
            $executeRkasPendapatan = Yii::$app->db->createCommand("
                DELETE FROM ta_rkas_belanja_rinc;
                DELETE FROM ta_rkas_belanja_rencana;
                DELETE FROM ta_rkas_belanja;
                DELETE FROM ta_rkas_kegiatan_penugasan;
                DELETE FROM ta_rkas_kegiatan_verifikasi;
                DELETE FROM ta_rkas_kegiatan;
                DELETE FROM ta_kegiatan_pemda_mapping;
                DELETE FROM ta_kegiatan_pemda;
                DELETE FROM ta_program_pemda;
            ")->execute();
            $executeBaver = Yii::$app->db->createCommand("
                DELETE FROM ta_baver_rinc;
                DELETE FROM ta_baver;
            ")->execute();
            $transaction->commit();
            $alertColor = "info";
            $result = "Penghapusan Berhasil";
        } catch (\Exception $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal: ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        } catch (\Throwable $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        }
        return true;
    }

    private function truncateParameter()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $executeUnit = Yii::$app->db->createCommand("
                DELETE FROM ta_unit_jab;
                DELETE FROM ta_info_bos;
                DELETE FROM ref_unit;
            ")->execute();
            $executePemda = Yii::$app->db->createCommand("
                DELETE FROM ta_th;
            ")->execute();
            $transaction->commit();
            $alertColor = "info";
            $result = "Penghapusan Berhasil";
        } catch (\Exception $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal: ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        } catch (\Throwable $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        }
        return true;
    }

    private function truncateVerifikasiAsetTetap()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $executeRinci = Yii::$app->db->createCommand("
                DELETE FROM ta_aset_tetap_ba_rinci;
            ")->execute();
            $executeSaldo = Yii::$app->db->createCommand("
                DELETE FROM ta_aset_tetap_ba_saldo;
            ")->execute();
            $executeBa = Yii::$app->db->createCommand("
                DELETE FROM ta_aset_tetap_ba_saldo;
            ")->execute();
            $executeKondisi = Yii::$app->db->createCommand("
                DELETE FROM ta_aset_tetap_kondisi;
            ")->execute();
            $transaction->commit();
            $alertColor = "info";
            $result = "Penghapusan Berhasil";
        } catch (\Exception $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal: ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        } catch (\Throwable $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        }
        return true;
    }

    private function truncateAsetTetap()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $executeRinci = Yii::$app->db->createCommand("
                DELETE FROM ta_aset_tetap_kondisi;
            ")->execute();
            $executeSaldo = Yii::$app->db->createCommand("
                DELETE FROM ta_aset_tetap;
            ")->execute();
            $transaction->commit();
            $alertColor = "info";
            $result = "Penghapusan Berhasil";
        } catch (\Exception $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal: ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        } catch (\Throwable $e) {
            $alertColor = "warning";
            $result = "Penghapusan Gagal ".$e;
            $transaction->rollBack();

            $this->addError('truncateLevel', $result);
            return false;
        }
        return true;
    }    
}
