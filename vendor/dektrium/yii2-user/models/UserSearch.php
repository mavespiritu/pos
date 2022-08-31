<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dektrium\user\models;

use dektrium\user\Finder;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about User.
 */
class UserSearch extends Model
{
    /** @var integer */
    public $id;

    /** @var string */
    public $username;

    /** @var string */
    public $email;

    /** @var int */
    public $created_at;

    /** @var int */
    public $last_login_at;

    /** @var string */
    public $registration_ip;

    /** @var Finder */
    protected $finder;

    public $has_role;
    
    public $BRANCH_C;

    public $SCHOOL_C;

    public $fullName;

    public $keyname;

    /**
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'fieldsSafe' => [['id', 'username', 'email', 'registration_ip', 'created_at', 'last_login_at', 'keyname', 'BRANCH_C', 'SCHOOL_C', 'fullName', 'has_role'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
            'lastloginDefault' => ['last_login_at', 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('user', '#'),
            'username'        => Yii::t('user', 'Username'),
            'email'           => Yii::t('user', 'Email'),
            'created_at'      => Yii::t('user', 'Registration time'),
            'last_login_at'   => Yii::t('user', 'Last login'),
            'registration_ip' => Yii::t('user', 'Registration ip'),
            'has_role'        => Yii::t('user', 'Has Access?'),
            'BRANCH_C'      => Yii::t('user', 'Branch'),
            'SCHOOL_C'      => Yii::t('user', 'School'),
            'fullName'      => Yii::t('user', 'Full Name'),
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $additionalParams = [])
    {
        $query = $this->finder->getUserQuery();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        $query->joinWith('userinfo');
        if(isset($additionalParams['and'])){
            foreach($additionalParams['and'] as $additionalParams):
                $query->andWhere($additionalParams);
            endforeach;
        }
        if(isset($additionalParams['or'])){
            foreach($additionalParams['or'] as $additionalParams):
                $query->orWhere($additionalParams);
            endforeach;
        }

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $modelClass = $query->modelClass;
        $table_name = $modelClass::tableName();

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', $table_name . '.created_at', $date, $date + 3600 * 24]);
        }

        $query->andFilterWhere(['like', $table_name . '.username', $this->username])
              ->andFilterWhere(['like', $table_name . '.email', $this->email])
              ->andFilterWhere(['like', 'user_info.BRANCH_C', $this->BRANCH_C])
              ->andFilterWhere(['like', 'user_info.SCHOOL_C', $this->SCHOOL_C])
              ->andFilterWhere(['like', 'concat(user_info.FIRST_M," ",user_info.MIDDLE_M," ",user_info.LAST_M," ",user_info.SUFFIX)', $this->fullName])
              ->andFilterWhere([$table_name . 'registration_ip' => $this->registration_ip]);

        $nameArray = explode(' ', $this->keyname);
        foreach($nameArray as $name):
            $query->andFilterWhere(['or', ['like', 'FIRST_M', $name], ['like', 'MIDDLE_M', $name], ['like', 'LAST_M', $name]]);
            endforeach;

        return $dataProvider;
    }
}
