<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_package".
 *
 * @property int $id
 * @property int $branch_id
 * @property int $package_type_id
 * @property int $tier
 * @property string $code
 * @property string $amount
 *
 * @property AccountingBranch $branch
 * @property AccountingPackageType $packageType
 * @property AccountingPackageFreebie[] $accountingPackageFreebies
 * @property AccountingPackageStudent[] $accountingPackageStudents
 */
class Package extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_package';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'season_id', 'package_type_id', 'tier', 'code', 'amount'], 'required'],
            [['branch_id', 'program_id', 'branch_program_id', 'season_id', 'package_type_id', 'tier'], 'integer'],
            [['amount'], 'number'],
            [['code'], 'string', 'max' => 250],
            [['branch_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchProgram::className(), 'targetAttribute' => ['branch_program_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['package_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PackageType::className(), 'targetAttribute' => ['package_type_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_id' => 'Branch',
            'branchName' => 'Branch',
            'program_id' => 'Program',
            'programName' => 'Program',
            'branch_program_id' => 'Branch - Program',
            'branchProgramName' => 'Branch - Program',
            'season_id' => 'Season',
            'seasonName' => 'Season',
            'package_type_id' => 'Package Type',
            'packageTypeName' => 'Package Type',
            'tier' => 'Tier',
            'code' => 'Code',
            'amount' => 'Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'program_id']);
    }

    public function getProgramName()
    {
        return $this->program ? $this->program->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }

    public function getBranchProgramName()
    {
        return $this->branchProgram ? $this->branchProgram->branchProgramName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageType()
    {
        return $this->hasOne(PackageType::className(), ['id' => 'package_type_id']);
    }

    public function getPackageTypeName()
    {
        return $this->packageType ? $this->packageType->enroleeType->name.' - '.$this->packageType->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageFreebies()
    {
        return $this->hasMany(PackageFreebie::className(), ['package_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageStudents()
    {
        return $this->hasMany(PackageStudent::className(), ['package_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoachings()
    {
        return $this->hasMany(Coaching::className(), ['package_id' => 'id']);
    }
}
