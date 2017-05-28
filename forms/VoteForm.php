<?php
Yii::import("application.modules.vote.voteModule");
Yii::import("application.modules.vote.models.*");
// import your model here
Yii::import("application.modules.blog.models.Post", true);

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package hauntd\vote\models
 */

/**
 * @author vulcangz <zhugd168@163.com>
 * @package vote
 */ 
class VoteForm extends yupe\models\YFormModel
{
    const ACTION_POSITIVE = 'positive';
    const ACTION_NEGATIVE = 'negative';
    const ACTION_TOGGLE = 'toggle';

    /**
     * @var string entity (e.g. "user.like" or "page.voting")
     */
    public $entity;

    /**
     * @var integer target model id
     */
    public $targetId;

    /**
     * @var string +/-?
     */
    public $action;

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        return [
            ['entity, targetId, action', 'required'],
            ['targetId', 'numerical', 'integerOnly' => true],
            ['action', 'in', 'range' => [self::ACTION_NEGATIVE, self::ACTION_POSITIVE, self::ACTION_TOGGLE]],
            ['entity', 'checkModel'],
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->action == self::ACTION_NEGATIVE ? VOTE::VOTE_NEGATIVE : VOTE::VOTE_POSITIVE;
    }

    /**
     * @return bool
     * @throws CException
     */
    public function checkModel()
    {
        $module = Yii::app()->getModule('vote');
        $settings = $module->getSettingsForEntity($this->entity);

        if ($settings === null) {
            $this->addError('entity', Yii::t('VoteModule.vote', 'This entity is not supported.'));
            return false;
        }

		if (Yii::app()->user->isGuest && ($settings['type'] == VoteModule::TYPE_TOGGLE)) {
            $this->addError('entity', Yii::t('VoteModule.vote', 'Guests are not allowed for this voting.'));
            return false;
        }

		$targetModel = new $settings['modelName'];
        if (($targetModel::model()->findByPk($this->targetId)) == null) {
            $this->addError('targetId', Yii::t('VoteModule.vote', 'Target model not found.'));
            return false;
        }

		$allowGuestsConfig = $module->getSettingsForEntity('siteVoteGuests');
		$allowGuests = $allowGuestsConfig['allowGuests'];
        if ($allowGuests == false && Yii::app()->user->isGuest) {
            $this->addError('entity', Yii::t('VoteModule.vote', 'Guests are not allowed for this voting.'));
            return false;
        }
        if ($allowGuests && Yii::app()->user->isGuest && $settings['type'] !== VoteModule::TYPE_VOTING) {
            $this->addError('entity', Yii::t('VoteModule.vote', 'Only voting is allowed for guests.'));
            return false;
        }

        return true;
    }
}