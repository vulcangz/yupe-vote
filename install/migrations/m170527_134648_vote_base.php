<?php

class m170527_134648_vote_base extends yupe\components\DbMigration
{
	public function safeUp()
	{
		// vote
        $this->createTable(
			'{{vote}}', 
			[
				'id' => "pk",
				'entity' => "integer unsigned NOT NULL",
				'target_id' => "integer NOT NULL",
				'user_id' => "integer DEFAULT NULL",
				'user_ip' => "varchar(39) not null default '127.0.0.1'",
				'value' => "boolean not null default '1'",
				'created_at' => "integer DEFAULT NULL",
			],
			$this->getOptions()
		);
		
		// vote_aggregate
        $this->createTable(
			'{{vote_aggregate}}', 
			[
				'id' => "pk",
				'entity' => "integer unsigned NOT NULL",
				'target_id' => "integer NOT NULL",
				'positive' => "integer DEFAULT 0",
				'negative' => "integer DEFAULT 0",
				'rating' => "float unsigned NOT NULL DEFAULT '0'",
			],
			$this->getOptions()
		);
		
		// ix
        $this->createIndex('vote_target_idx', '{{vote}}', 'entity, target_id', false);
        $this->createIndex('vote_user_idx', '{{vote}}', 'user_id', false);
        $this->createIndex('vote_user_ip_idx', '{{vote}}', 'user_ip', false);
        $this->createIndex('vote_aggregate_target_idx', '{{vote_aggregate}}', 'entity, target_id', true);
    }

	public function safeDown()
	{
        $this->dropTable('{{vote}}');
        $this->dropTable('{{vote_aggregate}}');
    }
}