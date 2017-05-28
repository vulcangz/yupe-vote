<?php
/**
 * This is the model class for table "{{vote_aggregate}}".
 *
 * The followings are the available columns in table '{{vote_aggregate}}':
 * @property integer $id
 * @property string $entity
 * @property integer $target_id
 * @property integer $positive
 * @property integer $negative
 * @property double $rating
 */

//class VoteAggregate extends CActiveRecord
class VoteAggregate extends yupe\models\YModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{vote_aggregate}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('entity, target_id, positive, negative, rating', 'required'),
			array('entity, target_id, positive, negative', 'numerical', 'integerOnly'=>true),
			array('rating', 'numerical'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, entity, target_id, positive, negative, rating', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
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
			'positive' => 'Positive',
			'negative' => 'Negative',
			'rating' => 'Rating',
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
		$criteria->compare('positive',$this->positive);
		$criteria->compare('negative',$this->negative);
		$criteria->compare('rating',$this->rating);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return VoteAggregate the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}