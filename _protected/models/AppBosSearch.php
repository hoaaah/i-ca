<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppBos;

/**
 * AppBosSearch represents the model behind the search form about `app\models\AppBos`.
 */
class AppBosSearch extends AppBos
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codename', 'version', 'performed_at'], 'safe'],
            [['release_number'], 'integer'],
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
        $query = AppBos::find();

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
            'release_number' => $this->release_number,
            'performed_at' => $this->performed_at,
        ]);

        $query->andFilterWhere(['like', 'codename', $this->codename])
            ->andFilterWhere(['like', 'version', $this->version]);

        return $dataProvider;
    }
}
