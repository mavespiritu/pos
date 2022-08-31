<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Notification;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\IncomeCode;
use common\modules\accounting\models\Expense;
use common\modules\accounting\models\BudgetProposal;
use common\modules\accounting\models\BranchTransfer;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\Particular;
use common\modules\accounting\models\Liquidation;
use common\modules\accounting\models\LiquidationCategory;
use common\modules\accounting\models\ParticularCode;
use common\modules\accounting\models\PfRequest;
use common\modules\accounting\models\BudgetProposalType;
use common\modules\accounting\models\BudgetProposalSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
/**
 * BudgetProposalController implements the CRUD actions for BudgetProposal model.
 */
class BudgetProposalController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'update' ,'view', 'approve', 'liquidate', 'delete', 'particular', 'particular-update', 'particular-delete', 'liquidation-update','liquidation-delete', 'pf-request', 'pf-request-update', 'pf-request-delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'particular', 'pf-request'],
                        'allow' => true,
                        'roles' => ['manageBudgetProposal'],
                    ],
                    [
                        'actions' => ['create', 'update', 'particular', 'particular-update', 'particular-delete', 'liquidation-update','liquidation-delete', 'pf-request', 'pf-request-update', 'pf-request-delete'],
                        'allow' => true,
                        'roles' => ['createBudgetProposal'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['viewBudgetProposal'],
                    ],
                    [
                        'actions' => ['approve'],
                        'allow' => true,
                        'roles' => ['approveBudgetProposal'],
                    ],
                    [
                        'actions' => ['liquidate'],
                        'allow' => true,
                        'roles' => ['liquidateBudgetProposal', 'manageBudgetProposal'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteBudgetProposal'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new BudgetProposal();
        $model->scenario = 'createBudgetProposal';

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
            ->andWhere(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->andWhere(['id' => $access->branch_program_id])
            ->all() : BranchProgram::find()
            ->andWhere(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->all() : BranchProgram::find()
            ->andWhere(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->all() ;
        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $budgetProposalTypes = BudgetProposalType::find()->asArray()->all();
        $budgetProposalTypes = ArrayHelper::map($budgetProposalTypes, 'id', 'name');

        if($model->load(Yii::$app->request->post()))
        {
            if($model->grantee == 'Branch')
            {
                $model->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
            }
            
            $model->code_id = 11;
            $model->approval_status = 'For Approval';
            if($model->save())
            {
                if($model->budgetProposalTypeName == 'Professional Fee')
                {
                    $notification = new Notification();
                    $notification->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                    $notification->model = 'BudgetProposal';
                    $notification->model_id = $model->id;
                    $notification->message = 'Your budget proposal with a request type of '.$model->budgetProposalTypeName.' needs particulars and attachment of professional fees to request amount. Click here to proceed.';
                    $notification->save();
                }else{
                    $notification = new Notification();
                    $notification->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                    $notification->model = 'BudgetProposal';
                    $notification->model_id = $model->id;
                    $notification->message = 'Your budget proposal with a request type of '.$model->budgetProposalTypeName.' needs particulars to request amount. Click here to proceed.';
                    $notification->save();
                }

                $notification2 = new Notification();
                $notification2->model = 'BudgetProposal';
                $notification2->model_id = $model->id;
                $notification2->message = $model->branchProgramName == '' ? 'The budget proposal requested by '.$model->branchName.' with a request type of '.$model->budgetProposalTypeName.' needs your approval. Click here to review.' : 'The budget proposal requested by '.$model->branchProgramName.' with a request type of '.$model->budgetProposalTypeName.' needs your approval. Click here to review.';
                $notification2->save();
            }
            
            \Yii::$app->getSession()->setFlash('success', 'Your budget proposal has been created. Fill-up the budget proposal sheet to encode details of proposal.');
            return $this->redirect(['particular', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'branchPrograms'=> $branchPrograms,
            'budgetProposalTypes' => $budgetProposalTypes,
        ]);
    }

    public function actionParticular($id)
    {
        $model = $this->findModel($id);
        
        $incomeModel = Income::find()->where(['income_id' => $model->id, 'income_type_id' => 3])->one() ? Income::find()->where(['income_id' => $model->id, 'income_type_id' => 3])->one() : new Income();
        
        $branchTransferModel = BranchTransfer::findOne(['budget_proposal_id' => $model->id]) ? BranchTransfer::findOne(['budget_proposal_id' => $model->id]) : new BranchTransfer();

        if(BranchTransfer::findOne(['budget_proposal_id' => $model->id]))
        {
             $branchTransferModel->grantee = $branchTransferModel->branch_id != '' ? 'Branch' : 'Branch - Program';
             if($branchTransferModel->grantee == 'Branch')
             {
                $branchTransferModel->branch_id = $branchTransferModel->branch_id;
             }else{
                $branchTransferModel->branch_program_id = $branchTransferModel->branch_program_id;
             }

            $expenseModel = Expense::find()->where(['expense_id' => $branchTransferModel->id, 'expense_type_id' => 6])->one();
        }else{
            $expenseModel = new Expense();
        }

        $branches = Branch::find()->orderBy(['name' => SORT_ASC])->all();
        $branches = ArrayHelper::map($branches, 'id', 'name');

        $branchPrograms = BranchProgram::find()
                            ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                            ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                            ->asArray()
                            ->orderBy(['branchProgramName' => SORT_ASC])
                            ->all();
        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $model->scenario = 'approveBudgetProposal';
        $branchTransferModel->scenario = 'approveBudgetProposal';
        $particular = new Particular();

        $particularCodes = ParticularCode::find()->select(['id', 'concat(name," - ",description) as descr'])->asArray()->orderBy(['descr' => SORT_ASC])->all();
        $particularCodes = ArrayHelper::map($particularCodes, 'id', 'descr');

        $requestedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $approvedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id, 'approval_status' => 'Approved'])->asArray()->one();
        $percentApproved = $requestedAmount['total'] > 0 ? ($approvedAmount['total']/$requestedAmount['total'])*100 : 0;

        $liquidatedAmount = Liquidation::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $liquidationPercentage = $approvedAmount['total'] > 0 ? ($liquidatedAmount['total']/$approvedAmount['total'])*100 : 0;
        $unliquidatedAmount = $approvedAmount['total'] - $liquidatedAmount['total'];
    
        $liquidationSummary = Liquidation::find()
                            ->select([
                                'accounting_budget_proposal_liquidation_category.expense_type_id',
                                'accounting_budget_proposal_liquidation_category.name',
                                'sum(accounting_budget_proposal_liquidation.amount) as total'
                            ])
                            ->leftJoin('accounting_budget_proposal_liquidation_category', 'accounting_budget_proposal_liquidation_category.id = accounting_budget_proposal_liquidation.category_id')
                            ->where(['accounting_budget_proposal_liquidation.budget_proposal_id' => $model->id])
                            ->groupBy(['accounting_budget_proposal_liquidation.category_id'])
                            ->orderBy(['accounting_budget_proposal_liquidation_category.id' => SORT_ASC])
                            ->asArray()
                            ->all();

        if($particular->load(Yii::$app->request->post()))
        {
            $particular->budget_proposal_id = $model->id;
            $particular->approval_status = 'For Approval';
            $particular->save();

            \Yii::$app->getSession()->setFlash('success', 'Particular has been saved. Fill-up the form to add more particulars.');
            return $this->redirect(['/accounting/budget-proposal/particular','id' => $model->id]);
        }

        if($model->load(Yii::$app->request->post()) || $branchTransferModel->load(Yii::$app->request->post()))
        {
            $postData = Yii::$app->request->post();
            $model->approval_status = $postData['BudgetProposal']['approval_status'];
            $model->remarks = $postData['BudgetProposal']['remarks'];
            if($model->save(false))
            {
                if($model->approval_status == "Approved")
                {
                    if(isset($postData['BranchTransfer']))
                    {
                        $incomeModel->income_type_id = 3;
                        if($model->branch_id != '')
                        {
                            $incomeModel->branch_id = $model->branch_id;
                        }else{
                            $branchProgram = BranchProgram::findOne($model->branch_program_id);
                            if($branchProgram)
                            {
                                $incomeModel->branch_id = $branchProgram->branch_id;
                                $incomeModel->program_id = $branchProgram->program_id;
                            }
                        }

                        $incomeModel->income_id = $model->id;
                        $incomeModel->amount_type = $postData['Income']['amount_type'];
                        if($incomeModel->save(false))
                        {
                            $notification = new Notification();
                            $notification->branch_id = $incomeModel->branch_id;
                            $notification->model = 'BudgetProposal';
                            $notification->model_id = $model->id;
                            $notification->message = 'Your budget proposal with a request type of '.$model->budgetProposalTypeName.' and approved amount of '.$model->approvedAmount.' has been reviewed by the top management. Click here to show more details';
                            $notification->save();

                            if($postData['BranchTransfer']['grantee'] == 'Branch')
                            {
                                $branchTransferModel->branch_id = $postData['BranchTransfer']['branch_id'];
                            }else{
                                $branchTransferModel->branch_program_id = $postData['BranchTransfer']['branch_program_id'];
                            }

                            $branchTransferModel->budget_proposal_id = $model->id;    
                            $branchTransferModel->amount = $model->approvedAmount;    
                            $branchTransferModel->amount_source = $postData['BranchTransfer']['amount_source'];

                            if($branchTransferModel->save(false))
                            {
                                $expenseModel->expense_type_id = 6;

                                if($postData['BranchTransfer']['grantee'] == 'Branch')
                                {
                                    $expenseModel->branch_id = $branchTransferModel->branch_id;
                                }else{
                                    $branchProgram = BranchProgram::findOne($postData['BranchTransfer']['branch_program_id']);
                                    if($branchProgram)
                                    {
                                        $expenseModel->branch_id = $branchProgram->branch_id;
                                        $expenseModel->program_id = $branchProgram->program_id;
                                    }
                                }

                                $expenseModel->expense_id = $branchTransferModel->id;
                                $expenseModel->amount_type = $postData['BranchTransfer']['amount_source'] == 'Cash On Hand' ? 'Cash' : 'Bank Deposit';
                                $expenseModel->save(false);

                                $notification = new Notification();
                                $notification->branch_id = $expenseModel->branch_id;
                                $notification->model = 'BranchTransfer';
                                $notification->model_id = $branchTransferModel->id;
                                $notification->message = 'A branch transfer has been charged to your branch/program from a budget proposal request type of '.$model->budgetProposalTypeName.' and approved amount of '.$model->approvedAmount.'. Click here to see details';
                                $notification->save();

                                \Yii::$app->getSession()->setFlash('success', 'Budget proposal has been reviewed and taken action. Branch transfer has been charged to the fund source');
                                return $this->redirect(['/accounting/budget-proposal/particular','id' => $model->id]);
                            }  
                        }
                    }
                }
            }
        }

        return $this->render('particular', [
            'model' => $model,
            'incomeModel' => $incomeModel,
            'branchTransferModel' => $branchTransferModel,
            'expenseModel' => $expenseModel,
            'branches' => $branches,
            'branchPrograms' => $branchPrograms,
            'particular' => $particular,
            'particularCodes' => $particularCodes,
            'requestedAmount' => $requestedAmount,
            'approvedAmount' => $approvedAmount,
            'percentApproved' => $percentApproved,
            'liquidatedAmount' => $liquidatedAmount,
            'liquidationPercentage' => $liquidationPercentage,
            'unliquidatedAmount' => $unliquidatedAmount,
            'liquidationSummary' => $liquidationSummary
        ]);
    }

    public function actionParticularUpdate($id)
    {
        $particular = $this->findParticular($id);
        $model = $this->findModel($particular->budgetProposal->id);

        $particularCodes = ParticularCode::find()->select(['id', 'concat(name," - ",description) as descr'])->asArray()->all();
        $particularCodes = ArrayHelper::map($particularCodes, 'id', 'descr');

        $requestedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $approvedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id, 'approval_status' => 'Approved'])->asArray()->one();
        $percentApproved = $requestedAmount['total'] > 0 ? ($approvedAmount['total']/$requestedAmount['total'])*100 : 0;

        $liquidatedAmount = Liquidation::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $liquidationPercentage = $approvedAmount['total'] > 0 ? ($liquidatedAmount['total']/$approvedAmount['total'])*100 : 0;
        $unliquidatedAmount = $approvedAmount['total'] - $liquidatedAmount['total'];

        $liquidationSummary = Liquidation::find()
                            ->select([
                                'accounting_budget_proposal_liquidation_category.expense_type_id',
                                'accounting_budget_proposal_liquidation_category.name',
                                'sum(accounting_budget_proposal_liquidation.amount) as total'
                            ])
                            ->leftJoin('accounting_budget_proposal_liquidation_category', 'accounting_budget_proposal_liquidation_category.id = accounting_budget_proposal_liquidation.category_id')
                            ->where(['accounting_budget_proposal_liquidation.budget_proposal_id' => $model->id])
                            ->groupBy(['accounting_budget_proposal_liquidation.category_id'])
                            ->orderBy(['accounting_budget_proposal_liquidation_category.id' => SORT_ASC])
                            ->asArray()
                            ->all();

        if($particular->load(Yii::$app->request->post()))
        {
            $particular->save();

            \Yii::$app->getSession()->setFlash('success', 'Particular has been updated. Fill-up the form to add more particulars.');
            return $this->redirect(['/accounting/budget-proposal/particular','id' => $model->id]);
        }

        return $this->render('particular', [
            'model' => $model,
            'particular' => $particular,
            'particularCodes' => $particularCodes,
            'requestedAmount' => $requestedAmount,
            'approvedAmount' => $approvedAmount,
            'percentApproved' => $percentApproved,
            'liquidatedAmount' => $liquidatedAmount,
            'liquidationPercentage' => $liquidationPercentage,
            'unliquidatedAmount' => $unliquidatedAmount,
            'liquidationSummary' => $liquidationSummary
        ]);
    }

    public function actionParticularDelete($id)
    {
        $particular = $this->findParticular($id);
        $model = $this->findModel($particular->budgetProposal->id);

        $particular->delete();

        \Yii::$app->getSession()->setFlash('success', 'Particular has been deleted. Fill-up the form to add more particulars.');
        return $this->redirect(['/accounting/budget-proposal/particular','id' => $model->id]);
    }

    public function actionLiquidate($id)
    {
        $model = $this->findModel($id);
        if($model->approval_status == 'Approved')
        {
            $liquidation = new Liquidation();

            $liquidationCategories = LiquidationCategory::find()->select(['id', 'name'])->asArray()->all();
            $liquidationCategories = ArrayHelper::map($liquidationCategories, 'id', 'name');

            $requestedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
            $approvedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id, 'approval_status' => 'Approved'])->asArray()->one();
            $percentApproved = $requestedAmount['total'] > 0 ? ($approvedAmount['total']/$requestedAmount['total'])*100 : 0;

            $liquidatedAmount = Liquidation::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
            $liquidationPercentage = $approvedAmount['total'] > 0 ? ($liquidatedAmount['total']/$approvedAmount['total'])*100 : 0;
            $unliquidatedAmount = $approvedAmount['total'] - $liquidatedAmount['total'];

            $liquidationSummary = Liquidation::find()
                            ->select([
                                'accounting_budget_proposal_liquidation_category.expense_type_id',
                                'accounting_budget_proposal_liquidation_category.name',
                                'sum(accounting_budget_proposal_liquidation.amount) as total'
                            ])
                            ->leftJoin('accounting_budget_proposal_liquidation_category', 'accounting_budget_proposal_liquidation_category.id = accounting_budget_proposal_liquidation.category_id')
                            ->where(['accounting_budget_proposal_liquidation.budget_proposal_id' => $model->id])
                            ->groupBy(['accounting_budget_proposal_liquidation.category_id'])
                            ->orderBy(['accounting_budget_proposal_liquidation_category.id' => SORT_ASC])
                            ->asArray()
                            ->all();

            if($liquidation->load(Yii::$app->request->post()))
            {
                $liquidation->budget_proposal_id = $model->id;
                $liquidation->save();

                \Yii::$app->getSession()->setFlash('success', 'Particular has been saved. Fill-up the form to add more particulars in liquidation.');
                return $this->redirect(['/accounting/budget-proposal/liquidate','id' => $model->id]);
            }

            return $this->render('liquidation', [
                'model' => $model,
                'liquidation' => $liquidation,
                'liquidationCategories' => $liquidationCategories,
                'requestedAmount' => $requestedAmount,
                'approvedAmount' => $approvedAmount,
                'percentApproved' => $percentApproved,
                'liquidatedAmount' => $liquidatedAmount,
                'liquidationPercentage' => $liquidationPercentage,
                'unliquidatedAmount' => $unliquidatedAmount,
                'liquidationSummary' => $liquidationSummary
            ]);

        }else{
            throw new ForbiddenHttpException('Not Allowed');
        }
    }

    public function actionLiquidationUpdate($id)
    {
        $liquidation = $this->findLiquidation($id);
        $model = $liquidation->budgetProposal;

        $liquidationCategories = LiquidationCategory::find()->select(['id', 'name'])->asArray()->all();
        $liquidationCategories = ArrayHelper::map($liquidationCategories, 'id', 'name');

        $requestedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $approvedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id, 'approval_status' => 'Approved'])->asArray()->one();
        $percentApproved = $requestedAmount['total'] > 0 ? ($approvedAmount['total']/$requestedAmount['total'])*100 : 0;

        $liquidatedAmount = Liquidation::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $liquidationPercentage = $approvedAmount['total'] > 0 ? ($liquidatedAmount['total']/$approvedAmount['total'])*100 : 0;
        $unliquidatedAmount = $approvedAmount['total'] - $liquidatedAmount['total'];

        $liquidationSummary = Liquidation::find()
                            ->select([
                                'accounting_budget_proposal_liquidation_category.expense_type_id',
                                'accounting_budget_proposal_liquidation_category.name',
                                'sum(accounting_budget_proposal_liquidation.amount) as total'
                            ])
                            ->leftJoin('accounting_budget_proposal_liquidation_category', 'accounting_budget_proposal_liquidation_category.id = accounting_budget_proposal_liquidation.category_id')
                            ->where(['accounting_budget_proposal_liquidation.budget_proposal_id' => $model->id])
                            ->groupBy(['accounting_budget_proposal_liquidation.category_id'])
                            ->orderBy(['accounting_budget_proposal_liquidation_category.id' => SORT_ASC])
                            ->asArray()
                            ->all();

        if($liquidation->load(Yii::$app->request->post()))
        {
            $liquidation->budget_proposal_id = $model->id;
            $liquidation->save();

            \Yii::$app->getSession()->setFlash('success', 'Particular has been saved. Fill-up the form to add more particulars in liquidation.');
            return $this->redirect(['/accounting/budget-proposal/liquidate','id' => $model->id]);
        }

        return $this->render('liquidation', [
            'model' => $model,
            'liquidation' => $liquidation,
            'liquidationCategories' => $liquidationCategories,
            'requestedAmount' => $requestedAmount,
            'approvedAmount' => $approvedAmount,
            'percentApproved' => $percentApproved,
            'liquidatedAmount' => $liquidatedAmount,
            'liquidationPercentage' => $liquidationPercentage,
            'unliquidatedAmount' => $unliquidatedAmount,
            'liquidationSummary' => $liquidationSummary
        ]);
    }

    public function actionLiquidationDelete($id)
    {
        $liquidation = $this->findLiquidation($id);
        $model = $liquidation->budgetProposal;

        $liquidation->delete(); 

        \Yii::$app->getSession()->setFlash('success', 'Particular has been deleted. Fill-up the form to add more particulars in liquidation.');
            return $this->redirect(['/accounting/budget-proposal/liquidate','id' => $model->id]);
    }

    public function actionPfRequest($id)
    {
        $model = $this->findModel($id);
        $pfRequest = new PfRequest();

        $requestedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $approvedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id, 'approval_status' => 'Approved'])->asArray()->one();
        $percentApproved = $requestedAmount['total'] > 0 ? ($approvedAmount['total']/$requestedAmount['total'])*100 : 0;

        $liquidatedAmount = Liquidation::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $liquidationPercentage = $approvedAmount['total'] > 0 ? ($liquidatedAmount['total']/$approvedAmount['total'])*100 : 0;
        $unliquidatedAmount = $approvedAmount['total'] - $liquidatedAmount['total'];
    
        $liquidationSummary = Liquidation::find()
                            ->select([
                                'accounting_budget_proposal_liquidation_category.expense_type_id',
                                'accounting_budget_proposal_liquidation_category.name',
                                'sum(accounting_budget_proposal_liquidation.amount) as total'
                            ])
                            ->leftJoin('accounting_budget_proposal_liquidation_category', 'accounting_budget_proposal_liquidation_category.id = accounting_budget_proposal_liquidation.category_id')
                            ->where(['accounting_budget_proposal_liquidation.budget_proposal_id' => $model->id])
                            ->groupBy(['accounting_budget_proposal_liquidation.category_id'])
                            ->orderBy(['accounting_budget_proposal_liquidation_category.id' => SORT_ASC])
                            ->asArray()
                            ->all();

        if($pfRequest->load(Yii::$app->request->post()))
        {
            $pfRequest->budget_proposal_id = $model->id;
            $pfRequest->time = $pfRequest->time.' - '.$pfRequest->end_time;
            $pfRequest->save(false);

            \Yii::$app->getSession()->setFlash('success', 'Professional fee has been saved. Fill-up the form to add more fees.');
            return $this->redirect(['/accounting/budget-proposal/pf-request','id' => $model->id]);
        }

        return $this->render('pf-request', [
            'model' => $model,
            'pfRequest' => $pfRequest,
            'requestedAmount' => $requestedAmount,
            'approvedAmount' => $approvedAmount,
            'percentApproved' => $percentApproved,
            'liquidatedAmount' => $liquidatedAmount,
            'liquidationPercentage' => $liquidationPercentage,
            'unliquidatedAmount' => $unliquidatedAmount,
            'liquidationSummary' => $liquidationSummary
        ]);
    }

    public function actionPfRequestUpdate($id)
    {
        $pfRequest = $this->findPfRequest($id);
        $time = explode(" - ", $pfRequest->time);
        $pfRequest->time = $time[0];
        $pfRequest->end_time = $time[1];
        $model = $pfRequest->budgetProposal;

        $requestedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $approvedAmount = Particular::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id, 'approval_status' => 'Approved'])->asArray()->one();
        $percentApproved = $requestedAmount['total'] > 0 ? ($approvedAmount['total']/$requestedAmount['total'])*100 : 0;

        $liquidatedAmount = Liquidation::find()->select(['COALESCE(sum(amount), 0) as total'])->where(['budget_proposal_id' => $model->id])->asArray()->one();
        $liquidationPercentage = $approvedAmount['total'] > 0 ? ($liquidatedAmount['total']/$approvedAmount['total'])*100 : 0;
        $unliquidatedAmount = $approvedAmount['total'] - $liquidatedAmount['total'];
    
        $liquidationSummary = Liquidation::find()
                            ->select([
                                'accounting_budget_proposal_liquidation_category.expense_type_id',
                                'accounting_budget_proposal_liquidation_category.name',
                                'sum(accounting_budget_proposal_liquidation.amount) as total'
                            ])
                            ->leftJoin('accounting_budget_proposal_liquidation_category', 'accounting_budget_proposal_liquidation_category.id = accounting_budget_proposal_liquidation.category_id')
                            ->where(['accounting_budget_proposal_liquidation.budget_proposal_id' => $model->id])
                            ->groupBy(['accounting_budget_proposal_liquidation.category_id'])
                            ->orderBy(['accounting_budget_proposal_liquidation_category.id' => SORT_ASC])
                            ->asArray()
                            ->all();

        if($pfRequest->load(Yii::$app->request->post()))
        {
            $pfRequest->budget_proposal_id = $model->id;
            $pfRequest->time = $pfRequest->time.' - '.$pfRequest->end_time;
            $pfRequest->save(false);

            \Yii::$app->getSession()->setFlash('success', 'Professional fee has been updated. Fill-up the form to add more fees.');
            return $this->redirect(['/accounting/budget-proposal/pf-request','id' => $model->id]);
        }

        return $this->render('pf-request', [
            'model' => $model,
            'pfRequest' => $pfRequest,
            'requestedAmount' => $requestedAmount,
            'approvedAmount' => $approvedAmount,
            'percentApproved' => $percentApproved,
            'liquidatedAmount' => $liquidatedAmount,
            'liquidationPercentage' => $liquidationPercentage,
            'unliquidatedAmount' => $unliquidatedAmount,
            'liquidationSummary' => $liquidationSummary
        ]);
    }

    public function actionPfRequestDelete($id)
    {
        $pfRequest = $this->findPfRequest($id);
        $model = $pfRequest->budgetProposal;

        $pfRequest->delete();

        \Yii::$app->getSession()->setFlash('success', 'Professional fee has been deleted. Fill-up the form to add more fees.');
        return $this->redirect(['/accounting/budget-proposal/pf-request','id' => $model->id]);
    }

    public function actionIndex()
    {
        $searchModel = new BudgetProposalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->grantee = $model->branch != '' ? 'Branch' : 'Branch - Program';

        $model->scenario = 'createBudgetProposal';

        $branchPrograms = BranchProgram::find()->where(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->all();
        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $budgetProposalTypes = BudgetProposalType::find()->asArray()->all();
        $budgetProposalTypes = ArrayHelper::map($budgetProposalTypes, 'id', 'name');

        if($model->load(Yii::$app->request->post()))
        {
            if($model->grantee == 'Branch')
            {
                $model->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                $model->branch_program_id = null;
            }
            
            $model->code_id = 11;
            $model->approval_status = 'For Approval';
            $model->save();
            
                \Yii::$app->getSession()->setFlash('success', 'Your budget proposal has been updated. Fill-up the budget proposal sheet to encode details of proposal.');
                return $this->redirect(['particular', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'branchPrograms'=> $branchPrograms,
            'budgetProposalTypes' => $budgetProposalTypes,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $model->delete();

        \Yii::$app->getSession()->setFlash('success', 'Budget Proposal has been deleted.');
        return $this->redirect(['index']);
    }

    public function actionParticularApprove($id)
    {
        $particular = $this->findParticular($id);
        $model = $this->findModel($particular->budgetProposal->id);

        if(Yii::$app->request->post())
        {
            $particular->approval_status = 'Approved';
            $particular->save();

        \Yii::$app->getSession()->setFlash('success', 'Particular has been approved.');
        return $this->redirect(['/accounting/budget-proposal/particular','id' => $model->id]);
        }
    }

    public function actionParticularDisapprove($id)
    {
        $particular = $this->findParticular($id);
        $model = $this->findModel($particular->budgetProposal->id);

        if(Yii::$app->request->post())
        {
            $particular->approval_status = 'Disapproved';
            $particular->save();

        \Yii::$app->getSession()->setFlash('success', 'Particular has been disapproved.');
        return $this->redirect(['/accounting/budget-proposal/particular','id' => $model->id]);
        }
    }

    public function actionShowBalance($grantee, $branch_id, $branch_program_id, $amount_source)
    {
        $total = 0;
        $cutoff = '';
        $label = '';
        if($grantee == 'Branch')
        {
            if($branch_id != '')
            {
                $branch = Branch::findOne($branch_id);
                if($branch)
                {
                    $data = $amount_source == 'Cash On Hand' ? $branch->cashOnHand : $branch->cashOnBank;

                    $total = ($data['beginningcoh'] + $data['incomeEnrolmentTotal'] + $data['freebiesTotal'] + $data['budgetProposalTotal']) - ($data['pettyExpenseTotal'] + $data['photocopyExpenseTotal'] + $data['otherExpenseTotal'] + $data['bankDepositsTotal'] + $data['operatingExpenseTotal'] + $data['branchTransferTotal']);

                    $cutoff = $data['cutoff']['start'].' - '.$data['cutoff']['end'];
                    $label = $branch->name;
                }
            }
        }else{
            if($branch_program_id != '')
            {
                $branchProgram = BranchProgram::findOne($branch_program_id);
                if($branchProgram)
                {
                    $data = $amount_source == 'Cash On Hand' ? $branchProgram->cashOnHand : $branchProgram->cashOnBank;
                        $total = ($data['beginningcoh'] + $data['incomeEnrolmentTotal'] + $data['freebiesTotal'] + $data['budgetProposalTotal']) - ($data['pettyExpenseTotal'] + $data['photocopyExpenseTotal'] + $data['otherExpenseTotal'] + $data['bankDepositsTotal'] + $data['operatingExpenseTotal'] + $data['branchTransferTotal']);

                        $cutoff = $data['cutoff']['start'].' - '.$data['cutoff']['end'];

                    $label = $branchProgram->branchProgramName;
                }
            }
        }

        return $this->renderAjax('_balance', [
            'total' => $total,
            'cutoff' => $cutoff,
            'amount_source' => $amount_source,
            'label' => $label,
        ]);
    }

    /**
     * Finds the BudgetProposal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BudgetProposal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BudgetProposal::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findParticular($id)
    {
        if (($model = Particular::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findLiquidation($id)
    {
        if (($model = Liquidation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findPfRequest($id)
    {
        if (($model = PfRequest::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
