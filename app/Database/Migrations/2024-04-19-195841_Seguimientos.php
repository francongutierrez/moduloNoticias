<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Seguimientos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'accion' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'motivo' => [
                'type' => 'VARCHAR',
                'constraint' =>1000,
                'null' => true
            ],
            'usuario' => [
                'type' => 'VARCHAR',
                'constraint' =>150
            ],
            'fechaCreacion' => [
                'type' => 'DATETIME',
            ],
            'id_noticia' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_noticia', 'noticias', 'id');
        $this->forge->createTable('seguimientos');
    }

    public function down()
    {
        $this->forge->dropTable('seguimientos');
    }
}
