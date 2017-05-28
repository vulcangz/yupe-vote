<?php
/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package hauntd\vote\widgets
 */

/** 
 * @author Vulcangz <zhugd168@163.com>
 * @package vote
 */

Yii::import('application.modules.vote.models.Vote');
Yii::import('application.modules.vote.components.behaviors.*');

//abstract class BaseWidget extends CWidget
abstract class BaseWidget extends yupe\widgets\YWidget
{
    /**
     * @var string
     */
    public $entity;

    /**
     * @var null|CActiveRecord
     */
    public $model;

    /**
     * @var null|integer;
     */
    public $targetId;

    /**
     * @var string
     */
    public $voteUrl;

    /**
     * @var null|vote.models.VoteAggregate
     */
    public $aggregateModel;

    /**
     * @var null|integer
     */
    public $userValue;

    /**
     * @var string
     */
    public $jsBeforeVote;

    /**
     * @var string
     */
    public $jsAfterVote;

    /**
     * @var string
     */
    public $jsCodeKey = 'vote';

    /**
     * @var string
     */
    public $jsErrorVote;

    /**
     * @var string
     */
    public $jsShowMessage;

    /**
     * @var string
     */
    public $jsChangeCounters;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string
     */
    public $viewFile = 'vote';

    /**
     * @var array
     */
    public $viewParams = [];

	/**
     * @var array
     */
    public $buttonOptions = [];

    /**
     * @var bool
     */
    protected $_behaviorIncluded;

    /**
     * @return string
     */
    public function getSelector()
    {
        $classes = str_replace(' ', '.', $this->options['class']);
        return ".{$classes}[data-entity=\"' + entity + '\"][data-target-id=\"' + target  + '\"]";
    }

    /**
     * @inheritdoc
     * @throws CException
     */
    public function init()
    {
        parent::init();

        if (!isset($this->entity) || !isset($this->model)) {
            throw new CException(Yii::t('voteModule.vote', 'Entity and model must be set.'));
        }

        $this->initDefaults();
    }

    /**
     * Initialize widget with default options.
     *
     * @throws CException
     */
    public function initDefaults()
    {
        $this->voteUrl = isset($this->voteUrl) ?: Yii::app()->createUrl('vote/default/vote');
        $this->targetId = isset($this->targetId) ?: $this->model->getPrimaryKey();
        $_entity = Yii::app()->getModule('vote')->encodeEntity($this->entity);

        if (!isset($this->aggregateModel)) {
            $this->aggregateModel = $this->isBehaviorIncluded() ?
                $this->model->getVoteAggregate($this->entity):
                VoteAggregate::model()->find('entity=:entity AND target_id=:target_id', [
					':entity' => $_entity,
					':target_id' => $this->targetId,
				]);
        }

        if (!isset($this->userValue)) {
            $this->userValue = $this->isBehaviorIncluded() ? $this->model->getUserValue($this->entity) : null;
        }
    }

    /**
     * Registers jQuery handler.
     */
    protected function registerJs()
    {
$jsCode = <<<EOD
	$('body').on('click', '[data-rel=\"{$this->jsCodeKey}\"] button', function(event) {
		var vote = $(this).closest('[data-rel=\"{$this->jsCodeKey}\"]'),
			button = $(this),
			action = button.attr('data-action'),
			entity = vote.attr('data-entity'),
			target = vote.attr('data-target-id');
			var data = {};
			data['VoteForm[entity]'] = entity;
			data['VoteForm[targetId]'] = target;
			data['VoteForm[action]'] = action;
			data[yupeTokenName] = yupeToken;
		jQuery.ajax({
			url: '$this->voteUrl', type: 'POST', dataType: 'json', cache: false,
			data,
			beforeSend: function(jqXHR, settings) { $this->jsBeforeVote },
			success: function(data, textStatus, jqXHR) { $this->jsChangeCounters $this->jsShowMessage },
			complete: function(jqXHR, textStatus) { $this->jsAfterVote },
			error: function(jqXHR, textStatus, errorThrown) { $this->jsErrorVote }
		});
	});
EOD;
		$assetsDir=dirname(__FILE__).DIRECTORY_SEPARATOR.'assets';
        $assets=Yii::app()->assetManager->publish($assetsDir);
		$cs=Yii::app()->getClientScript();
        $cs->registerCssFile($assets.'/css/vote.css');
		$cs->registerScript($this->jsCodeKey, $jsCode, CClientScript::POS_END);		
    }

    /**
     * @param array $params
     * @return array
     */
    protected function getViewParams(array $params)
    {
        return array_merge($this->viewParams, $params);
    }

    /**
     * @return bool
     */
    protected function isBehaviorIncluded()
    {
        if (isset($this->_behaviorIncluded)) {
            return $this->_behaviorIncluded;
        }

        if (!isset($this->aggregateModel) || !isset($this->userValue)) {
            foreach ($this->model->behaviors() as $behavior) {
				if (strpos($behavior['class'],  "vote.components.behaviors.VoteBehavior") !== false)
					return $this->_behaviorIncluded = true;
            }
        }

        return $this->_behaviorIncluded = false;
    }	
}