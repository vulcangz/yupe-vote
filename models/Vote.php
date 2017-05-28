<?php
/**
 * This is the model class for table "{{vote}}".
 *
 * The followings are the available columns in table '{{vote}}':
 * @property integer $id
 * @property string $entity
 * @property integer $target_id
 * @property integer $user_id
 * @property string $user_ip
 * @property integer $value
 * @property integer $created_at
 */

class Vote extends CActiveRecord
//class Vote extends yupe\models\YModel
{
	/**
     *
     */
    const VOTE_POSITIVE = 1;
	/**
     *
     */
    const VOTE_NEGATIVE = 0;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{vote}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('entity, target_id, value', 'required'),
			array('target_id, user_id, value, created_at', 'numerical', 'integerOnly'=>true),
			/*array('user_ip', 'default', 'value'=> function(){
				return Yii::app()->request->userHostAddress;
			}),
			array('user_id', 'default', 'value'=> function(){
				if (!Yii::app()->user->isGuest) {
					return Yii::app()->user->id;
				}
				return null;
				}),*/
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, entity, target_id, user_id, user_ip, value, created_at', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
		];
	}

	/**
	 * @return array
     */
    public function behaviors()
    {
        return [
            'CTimestampBehavior' => [
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'created_at',
				'updateAttribute' => null,
            ],
        ];
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'entity' => 'Entity',
			'target_id' => 'Target',
			'user_id' => 'User',
			'user_ip' => 'User Ip',
			'value' => 'Value',
			'created_at' => 'Created At',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('entity',$this->entity,true);
		$criteria->compare('target_id',$this->target_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('user_ip',$this->user_ip,true);
		$criteria->compare('value',$this->value);
		$criteria->compare('created_at',$this->created_at);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Vote the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
    }

	/**
     * @param bool $insert
     * @param array $changedAttributes
     */
	public function afterSave()
    {
        static::updateRating($this->attributes['entity'], $this->attributes['target_id']);
        parent::afterSave();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        static::updateRating($this->attributes['entity'], $this->attributes['target_id']);
        parent::afterDelete();
    }

    /**
     * @param $entity
     * @param $targetId
     */
    public static function updateRating($entity, $targetId)
    {
		$positive = Yii::app()->db->createCommand()
			->select('count(*)')
			->from('{{vote}}')
			->where('entity=:entity AND target_id=:target_id AND value='.self::VOTE_POSITIVE, array(':entity' => $entity, ':target_id' => $targetId))
			->queryScalar();

		$negative = Yii::app()->db->createCommand()
			->select('count(*)')
			->from('{{vote}}')
			->where('entity=:entity AND target_id=:target_id AND value='.self::VOTE_NEGATIVE, array(':entity' => $entity, ':target_id' => $targetId))
			->queryScalar();

        if ($positive + $negative !== 0) {
            $rating = (($positive + 1.9208) / ($positive + $negative) - 1.96 * SQRT(($positive * $negative)
                        / ($positive + $negative) + 0.9604) / ($positive + $negative)) / (1 + 3.8416 / ($positive + $negative));
        } else {
            $rating = 0;
        }
        $rating = round($rating * 10, 2);

		$criteria=new CDbCriteria();
		$criteria->condition='entity = :entity AND target_id = :target_id';
		$criteria->params[':entity']=$entity;
		$criteria->params[':target_id']=$targetId;

		$aggregateModel=VoteAggregate::model()->find($criteria);

        if ($aggregateModel == null) {
            $aggregateModel = new VoteAggregate();
            $aggregateModel->entity = $entity;
            $aggregateModel->target_id = $targetId;
        }
        $aggregateModel->positive = $positive;
        $aggregateModel->negative = $negative;
        $aggregateModel->rating = $rating;
        $aggregateModel->save();
    }
}