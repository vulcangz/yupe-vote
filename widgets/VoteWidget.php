<?php
/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package vote.widgets
 */

/** 
 * @author Vulcangz <zhugd168@163.com>
 * @package vote
 */

require_once 'BaseWidget.php';

class VoteWidget extends BaseWidget
{
    /**
     * @var string
     */
    public $jsCodeKey = 'vote';
	/**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'class' => 'vote',
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\CException
     */
    public function init()
    {
        parent::init();
		$this->options = array_merge($this->getDefaultOptions(), $this->options);
        $this->initJsEvents($this->getSelector());
        $this->registerJs();
    }

    /**
     * @return string
     * @throws \yii\base\CException
     */
    public function run()
    {
        $this->render($this->viewFile, $this->getViewParams([
            'jsCodeKey' => $this->jsCodeKey,
            'entity' => $this->entity,
            'model' => $this->model,
            'targetId' => $this->targetId,
            'userValue' => $this->userValue,
            'positive' => isset($this->aggregateModel->positive) ? $this->aggregateModel->positive : 0,
            'negative' => isset($this->aggregateModel->negative) ? $this->aggregateModel->negative : 0,
            'rating' => isset($this->aggregateModel->rating) ? $this->aggregateModel->rating : 0.0,
            'options' => $this->options,
        ]));
    }

    /**
     * Initialize with default events.
     *
     * @param string $selector
     */
    public function initJsEvents($selector)
    {
        if (!isset($this->jsBeforeVote)) {
            $this->jsBeforeVote = "
                $('$selector .vote-btn').prop('disabled', 'disabled').addClass('vote-loading');
                $('$selector .vote-count')
                    .addClass('vote-loading')
                    .append('<div class=\"vote-loader\"><span></span><span></span><span></span></div>');
            ";
        }
        if (!isset($this->jsAfterVote)) {
            $this->jsAfterVote = "
                $('$selector .vote-btn').prop('disabled', false).removeClass('vote-loading');
                $('$selector .vote-count').removeClass('vote-loading').find('.vote-loader').remove();
            ";
        }
        if (!isset($this->jsChangeCounters)) {
            $this->jsChangeCounters = "
                if (data.success) {
                    $('$selector .vote-count span').text(data.aggregate.positive - data.aggregate.negative);
                    vote.find('button').removeClass('vote-active');
                    button.addClass('vote-active');					
                }				
				if (data.message) $.notify(data.message);
            ";
        }
    }
}
