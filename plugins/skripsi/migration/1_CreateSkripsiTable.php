<?php
/**
 * @Created by      : Mohammad Nazir Arifin (nazir@unira.ac.id)
 * @Created on      : 2023-08-15 10:00:00
 * @Filename        : 1_CreateFreeLoanTable.php
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the MIT License. Please read MIT License for
 * more details. <https://opensource.org/licenses/MIT>
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * You should have received a copy of the MIT License along with this
 * program. If not, see <https://opensource.org/licenses/MIT>.
 */

use SLiMS\Table\Schema;
use SLiMS\Table\Blueprint;

class CreateSkripsiTable extends \SLiMS\Migration\Migration {
  function up() {
    Schema::create('skripsi', function(Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->charset = 'utf8mb4';
      $table->collation = 'utf8mb4_unicode_ci';
      $table->autoIncrement('id');
      $table->string('member_id', 20)->notNull();
      $table->string('title', 255)->notNull();
      $table->string('year', 4)->notNull();
      $table->string('file', 255)->notNull();
      $table->number('is_valid', 1)->notNull();
      $table->datetime('created_at')->notNull();
      $table->datetime('updated_at')->notNull();
    });
  }

  function down() {
    Schema::drop('skripsi');
  }
}