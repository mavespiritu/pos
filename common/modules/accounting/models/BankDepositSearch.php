<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\BankDeposit;

/**
 * BankDepositSearch represents the model behind the search form of `common\modules\accounting\models\BankDeposit`.
 */
class BankDepositSearch extends BankDeposit
{
    public $seasonName;
    public $amountType;
    public $datetime;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['bank', 'seasonName', 'account_no', 'transaction_no', 'deposited_by', 'remarks', 'amountType', 'datetime'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);
        $archivedSeasons = ArchiveSeason::find()->select(['season_id as id'])->asArray()->all();
        $archivedSeasons = ArrayHelper::map($archivedSeasons, 'id', 'id');

        if(in_array('TopManagement',$rolenames)){
            $query = $access ? $access->branch_program_id!= '' ? BankDeposit::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.expense_type_id' => '4'])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BankDeposit::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.expense_type_id' => '4'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BankDeposit::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.expense_type_id' => '4'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? BankDeposit::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_expense.expense_type_id' => '4'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BankDeposit::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense.expense_type_id' => '4'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BankDeposit::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense.expense_type_id' => '4'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                ;
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'bank',
                'account_no',
                'transaction_no',
                'deposited_by',
                'remarks',
                'seasonName' => [
                    'asc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_ASC],
                    'desc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_DESC],
                ],
                'amountType' => [
                    'asc' => ['accounting_expense.amount_type' => SORT_ASC],
                    'desc' => ['accounting_expense.amount_type' => SORT_DESC],
                ],
                'datetime' => [
                    'asc' => ['accounting_expense.datetime' => SORT_ASC],
                    'desc' => ['accounting_expense.datetime' => SORT_DESC],
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)', $this->seasonName])
            ->andFilterWhere(['like', 'bank', $this->bank])
            ->andFilterWhere(['like', 'account_no', $this->account_no])
            ->andFilterWhere(['like', 'transaction_no', $this->transaction_no])
            ->andFilterWhere(['like', 'deposited_by', $this->deposited_by])
            ->andFilterWhere(['like', 'remarks', $this->remarks]);

        $query->andFilterWhere(['like', 'accounting_expense.amount_type', $this->amountType])
              ->andFilterWhere(['like', 'accounting_expense.datetime', $this->datetime])
        ;


        return $dataProvider;
    }
}
