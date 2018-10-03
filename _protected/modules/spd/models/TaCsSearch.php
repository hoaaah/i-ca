<?php

namespace app\modules\spd\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TaCs;

/**
 * TaCsSearch represents the model behind the search form about `app\models\TaCs`.
 */
class TaCsSearch extends TaCs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cs_id', 'id_group', 'cs_n', 'ttd_pengaju', 'ttd_setuju_1', 'ttd_setuju_2', 'unit_id', 'sub_unit_id'], 'integer'],
            [['cs_no', 'st_no', 'st_tgl', 'maksud_perjalanan', 'beban_instansi', 'kode_anggaran', 'cs_tgl', 'cs_pengaju_nip', 'cs_pengaju_nama', 'cs_pengaju_jabatan', 'cs_setuju_1_tgl', 'cs_setuju_1_nip', 'cs_setuju_1_nama', 'cs_setuju_1_jabatan', 'cs_setuju_2_tgl', 'cs_setuju_2_nip', 'cs_setuju_2_nama', 'cs_setuju_2_jabatan', 'u_insert', 'date_insert', 'id'], 'safe'],
            [['cs_anggaran', 'cs_realisasi_lalu'], 'number'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TaCs::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'cs_id' => $this->cs_id,
            'id_group' => $this->id_group,
            'cs_n' => $this->cs_n,
            'st_tgl' => $this->st_tgl,
            'cs_tgl' => $this->cs_tgl,
            'cs_setuju_1_tgl' => $this->cs_setuju_1_tgl,
            'cs_setuju_2_tgl' => $this->cs_setuju_2_tgl,
            'ttd_pengaju' => $this->ttd_pengaju,
            'ttd_setuju_1' => $this->ttd_setuju_1,
            'ttd_setuju_2' => $this->ttd_setuju_2,
            'cs_anggaran' => $this->cs_anggaran,
            'cs_realisasi_lalu' => $this->cs_realisasi_lalu,
            'date_insert' => $this->date_insert,
            'unit_id' => $this->unit_id,
            'sub_unit_id' => $this->sub_unit_id,
        ]);

        $query->andFilterWhere(['ilike', 'cs_no', $this->cs_no])
            ->andFilterWhere(['ilike', 'st_no', $this->st_no])
            ->andFilterWhere(['ilike', 'maksud_perjalanan', $this->maksud_perjalanan])
            ->andFilterWhere(['ilike', 'beban_instansi', $this->beban_instansi])
            ->andFilterWhere(['ilike', 'kode_anggaran', $this->kode_anggaran])
            ->andFilterWhere(['ilike', 'cs_pengaju_nip', $this->cs_pengaju_nip])
            ->andFilterWhere(['ilike', 'cs_pengaju_nama', $this->cs_pengaju_nama])
            ->andFilterWhere(['ilike', 'cs_pengaju_jabatan', $this->cs_pengaju_jabatan])
            ->andFilterWhere(['ilike', 'cs_setuju_1_nip', $this->cs_setuju_1_nip])
            ->andFilterWhere(['ilike', 'cs_setuju_1_nama', $this->cs_setuju_1_nama])
            ->andFilterWhere(['ilike', 'cs_setuju_1_jabatan', $this->cs_setuju_1_jabatan])
            ->andFilterWhere(['ilike', 'cs_setuju_2_nip', $this->cs_setuju_2_nip])
            ->andFilterWhere(['ilike', 'cs_setuju_2_nama', $this->cs_setuju_2_nama])
            ->andFilterWhere(['ilike', 'cs_setuju_2_jabatan', $this->cs_setuju_2_jabatan])
            ->andFilterWhere(['ilike', 'u_insert', $this->u_insert])
            ->andFilterWhere(['ilike', 'id', $this->id]);

        return $dataProvider;
    }
}
