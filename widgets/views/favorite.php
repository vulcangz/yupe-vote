<?php

//Yii::import('application.modules.vote.models.Vote');

// defined in Model Vote
//const VOTE_POSITIVE = 1;
//const VOTE_NEGATIVE = 0;

//use hauntd\vote\models\Vote;
//use yii\helpers\Html;

/* @var $jsCodeKey string */
/* @var $entity string */
/* @var $model \yii\db\ActiveRecord */
/* @var $targetId integer */
/* @var $userValue null|integer */
/* @var $count integer */
/* @var $options array */
/* @var $buttonOptions array */

?>
<div class="<?= $options['class'] ?>"
     data-rel="<?= $jsCodeKey ?>"
     data-entity="<?= $entity ?>"
     data-target-id="<?= $targetId ?>"
     data-user-value="<?= $userValue ?>">
    <button class="vote-btn <?= $buttonOptions['class'] ?> <?= $userValue === Vote::VOTE_POSITIVE ? 'vote-active' : '' ?>"
            data-label-add="<?= CHtml::encode($buttonOptions['labelAdd']) ?>"
            data-label-remove="<?= CHtml::encode($buttonOptions['labelRemove']) ?>"
            data-action="toggle">
        <span class="vote-icon"><?= $buttonOptions['icon'] ?></span>
        <span class="vote-label">
            <?= CHtml::encode($buttonOptions[$userValue == Vote::VOTE_POSITIVE ? 'labelRemove' : 'labelAdd']) ?>
        </span>
        <span class="vote-count"><?= $count ?></span>
    </button>
</div>
