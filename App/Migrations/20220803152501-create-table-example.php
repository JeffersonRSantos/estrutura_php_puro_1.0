<?php

// Migration: create-table-tb-auth-forget-password
class m20220803152501 extends \Core\Migrations {
	function up() {
		$table = $this->table("tb_auth_forget_password");
		$table->increments("id");
		$table->integer("user_id_in100")->nullable();
		$table->integer("user_id_lead")->nullable();
		$table->string("token");
		$table->make();
	}
	function down() {
		self::dropTable("tb_auth_forget_password");
	}
}