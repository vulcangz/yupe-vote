<?php
/**
 * Class VoteBehavior
 * @package vote.components.behaviors
 */

/** 
 * @author Vulcangz <zhugd168@163.com>
 * @package vote
 */
class VoteBehavior extends CActiveRecordBehavior
{
	public $entity;
    /**
     * @var array
     */
    protected $voteAttributes;
	
	/**
     * @var bool
     */
    protected $selectAdded = false;
	/**
     * attributes already loaded from DB or not after find model.
     *
     * @var bool
     */
    protected $voteAttributesAreLoaded = false;
	
    /**
     * @access public
     * @var boolean loaded attributes after find model.
     * @default TRUE
     */
    public $preload = TRUE;
	
	private $_primaryKey;
    private $_tableSchema;
    private $_tableName;

    /**
     * @param \CComponent $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
    }
	
	/**
     * @param CEvent
     * @return void
     */
    public function afterFind($event) {
        // Load attributes for model.        
		if ( !$this->voteAttributesAreLoaded ) {
			$this->voteAttributes = $this->getVoteAttributes();

            $this->voteAttributesAreLoaded = true;
		}
        // Call parent method for convenience.
        parent::afterFind($event);
    }

    /**
     * @param $name
     * @return VoteAggregate|null
     * @throws CException
     */
    public function getVoteAggregate($name)
    {
        $entities = Yii::app()->getModule('vote')->entities;
        if (isset($entities[$name])) {
			$_voteAggregate = new VoteAggregate();
			$_voteAggregate->entity = Yii::app()->getModule('vote')->encodeEntity($name);
			$_voteAggregate->target_id = $this->getOwner()->getPrimaryKey();
			$_voteAggregate->positive = $this->getValue($this->voteAttributes, ["{$name}Positive"]);
			$_voteAggregate->negative = $this->getValue($this->voteAttributes, ["{$name}Negative"]);
			$_voteAggregate->rating = $this->getValue($this->voteAttributes, ["{$name}Rating"]);
            return $_voteAggregate;
        }
        return null;
    }

    /**
     * @param $name
     * @return null|integer
     * @throws CException
     */
    public function getUserValue($name)
    {
        $entities = Yii::app()->getModule('vote')->entities;
        if (isset($entities[$name])) {
            return $this->getValue($this->voteAttributes, ["{$name}UserValue"]);
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws CException
     */
    public function __set($name, $value)
    {
        if ($this->checkAttribute($name)) {
            $this->voteAttributes[$name] = !is_null($value) ? (int) $value : null;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @param $name
     * @return bool
     */
    protected function checkAttribute($name)
    {
        foreach (array_keys(Yii::app()->getModule('vote')->entities) as $entity) {
            if ($name == "{$entity}Positive" || $name == "{$entity}Negative" || $name == "{$entity}Rating" ||
                $name == "{$entity}UserValue") {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @param bool|true $checkVars
     * @return bool
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if (isset($this->voteAttributes[$name]) || $this->checkAttribute($name)) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @param string $name
     * @param bool|true $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if ($this->checkAttribute($name)) {
            return true;
        }
        return parent::canSetProperty($name, $checkVars);
    }

	/**
     * Prepares search criteria to find vote aggregate model/values attached to the model.
     *     
     * @return array voteAttributes values attached to the model.
     *     
     */
    protected function getVoteAttributes()
	{
		$voteTable = '{{vote}}';
		$voteAggregateTable = '{{vote_aggregate}}';
		
		$module = Yii::app()->getModule('vote');		
        $settings = $module->getSettingsForEntity($this->entity);
		
		$entities = [];
		foreach (array_keys($module->entities) as $key=>$val){
			if ((strpos($val, $settings['modelName']) !== false) && (strpos($val, 'Guests') === false)) {
				$entities[] = $val;
			}
		}
		
		$_select1 = null;
		$_select2 = null;
		$_join1 = null;
		$_join2 = null;
		$_join2and = null;

		foreach ($entities as $entity) {
			$entityEncoded[$entity] = $module->encodeEntity($entity);
			$_select1 .=  ", `{$entity}Aggregate`.`positive` as `{$entity}Positive`, `{$entity}Aggregate`.`negative` as `{$entity}Negative`, `{$entity}Aggregate`.`rating` as `{$entity}Rating`";
			$_select2 .= ", `{$entity}`.`value` as `{$entity}UserValue`";

			$_join1 .= sprintf(
				'LEFT JOIN %s ON (%s = t.`id`) AND (%s = %s) ',
				"$voteAggregateTable {$entity}Aggregate",
				"`{$entity}Aggregate`.`target_id`",
				"`{$entity}Aggregate`.`entity`",
				$entityEncoded[$entity]
			);
			$_join2 .= sprintf(
				'LEFT JOIN %s ON (%s = %s) AND (%s = t.`id`) ',
				"$voteTable {$entity}",
				"`{$entity}`.`entity`",
				$entityEncoded[$entity],
				"`{$entity}`.`target_id`"
			);

			if (Yii::app()->getUser()->isGuest) {
				$_join2and = sprintf(
					'AND %s AND %s',
					"{$entity}.user_ip = :user_ip",
					"{$entity}.user_id = :user_id"
				);
				$_join2param = array(
					':user_ip' => Yii::app()->getRequest()->userHostAddress,
					':user_id' => null
				);
			} else {
				$_join2and = sprintf(
				'AND %s',
				"{$entity}.user_id = :user_id");
				$_join2param = array(':user_id' => Yii::app()->getUser()->getId());
			}
        }

		$builder = $this->getOwner()->getCommandBuilder();

		$voteAttrsCriteria = new CDbCriteria();
		$voteAttrsCriteria->select = ltrim($_select1 . $_select2, ",");
		$voteAttrsCriteria->condition = sprintf(
			'`status`=1 AND `slug` = "%s"',
			$this->getOwner()->slug
		);

        $voteAttrsCriteria->join = $_join1 . $_join2 . $_join2and;
		$voteAttrsCriteria->params = $_join2param;

		$result = $builder->createFindCommand($this->tableName, $voteAttrsCriteria)->queryRow();

        return $result;
    }
	
	protected function cached($model=null)
    {
        if ($model === null)
            $model = $this->getOwner();

        $connection = $model->getDbConnection();
        return $model->cache($connection->queryCachingDuration);
    }

    protected function getTableName()
    {
        if ($this->_tableName === null)
            $this->_tableName = $this->getOwner()->tableName();
        return $this->_tableName;
    }
		
	/**
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array or object, the default value will be returned instead.
     *
	 * copy from yii2\helpers\BaseArrayHelper.php
	 */
	 /**
	 * BaseArrayHelper provides concrete implementation for [[ArrayHelper]].
	 *
	 * Do not use BaseArrayHelper. Use [[ArrayHelper]] instead.
	 *
	 * @author Qiang Xue <qiang.xue@gmail.com>
	 * @since 2.0
	 */
	private function getValue($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array)) ) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->$key;
        } elseif (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }
}