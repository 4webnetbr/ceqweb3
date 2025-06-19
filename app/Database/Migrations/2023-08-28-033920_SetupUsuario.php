<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConfigUsuario extends Migration
{
    protected $DBGroup = 'default';

    public function up()
    {
        $this->forge->addField([
            'usu_id' => [
                'type'          => 'INT',
                'constraint'    => 5,
                'unsignet'      => true,
                'auto_increment' => true,
                'comment'       => 'Id do Usuário'
            ],
            'usu_nome' => [
                'type'          => 'VARCHAR',
                'constraint'    => 30,
                'comment'       => 'Nome'
            ],
            'usu_login' => [
                'type'          => 'VARCHAR',
                'constraint'    => 50,
                'comment'       => 'Login'
            ],
            'usu_senha' => [
                'type'          => 'VARCHAR',
                'constraint'    => 50,
                'comment'       => 'Senha'
            ],
            'usu_email' => [
                'type'          => 'VARCHAR',
                'constraint'    => 100,
                'comment'       => 'Email'
            ],
            'usu_excluido' => [
                'type'          => 'DATETIME',
                'null'          => true,
            ],

        ]);
        $this->forge->addKey('usu_id', true);
        $this->forge->addUniqueKey(['usu_login']);
        $this->forge->createTable('config_usuario');
    }

    public function down()
    {
        $this->forge->dropTable('config_usuario');
    }
}
