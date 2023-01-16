<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Set the data
        $configs = array(
        array('key' => 'dc_size', 'value' => '2', 'description' => 'Tamaño códigos centro distribución'),
        array('key' => 'warehouse_size', 'value' => '3', 'description' => 'Tamaño códigos bodegas'),
        array('key' => 'zone_size', 'value' => '2', 'description' => 'Tamaño códigos zonas'),
        array('key' => 'module_size', 'value' => '2', 'description' => 'Tamaño códigos modulos'),
        array('key' => 'row_size', 'value' => '2', 'description' => 'Tamaño códigos filas'),
        array('key' => 'level_size', 'value' => '2', 'description' => 'Tamaño códigos nivel'),
        array('key' => 'receipt_group', 'value' => 'Etiqueteo y empaque', 'description' => 'Grupo de los empleados para recibir contenedores'),
        array('key' => 'stock_group', 'value' => 'Etiqueteo y empaque', 'description' => 'Grupo de los empleados para hacer inventario'),
        array('key' => 'receipt_machine', 'value' => 'Montacarga', 'description' => 'Tipo de máquina para recibir mercancia'),
        array('key' => 'stock_counts', 'value' => '3', 'description' => 'Cantidad de conteos para inventario'),
        array('key' => 'container_pallets', 'value' => '36', 'description' => 'Equivalencia estivas por contenedor'),
        array('key' => 'leader_charge', 'value' => 'Líder de bodega', 'description' => 'Cargo principal responsable de las tareas por bodega'),
        array('key' => 'cedi_charge', 'value' => 'Jefe de Cedi', 'description' => 'Cargo principal responsable del centro de distribución'),
        array('key' => 'foreign_trade', 'value' => 'Comercio Exterior', 'description' => 'grupo de comercio exterior'),
        array('key' => 'picking_type', 'value' => 'Picking', 'description' => 'Nombre del tipo de zona asignado para el picking'),
        array('key' => 'stand_type', 'value' => 'Estantería', 'description' => 'Nombre del tipo de zona asignado para el almacenamiento'),

        array('key' => 'concept_unavailable', 'value' => 'No existente', 'description' => 'Concepto de la posición no existente'),
        array('key' => 'feature_capacity', 'value' => 'Capacidad (kg)', 'description' => 'Caracteristica de capacidad'),
        array('key' => 'feature_weight', 'value' => 'Peso (kg)', 'description' => 'Caracteristica de peso'),
        array('key' => 'feature_height', 'value' => 'Alto (mts)', 'description' => 'Caracteristica de alto'),
        array('key' => 'feature_width', 'value' => 'Ancho (mts)', 'description' => 'Caracteristica de ancho'),
        array('key' => 'zone_concept_work_area', 'value' => 'Área de Trabajo', 'description' => 'Concepto de Zona de Trabajo'),
        array('key' => 'document_type_departure', 'value' => 'departure', 'description' => 'Tipo de documento'),
        array('key' => 'inspection_zone', 'value' => 'Zona de Inspección Ppal', 'description' => 'Concepto de Zona de Inspección'),
        array('key' => 'inportation_zone', 'value' => 'Zona de Importación', 'description' => 'Concepto de Zona de Importación'),
        array('key' => 'schema_category', 'value' => 'Línea', 'description' => 'Categorias con productos de linea'),
        array('key' => 'transit_receive', 'value' => 'Recibo transitorio', 'description' => 'Zona de recibo transitorio'),
        array('key' => 'dispatch', 'value' => 'Zona de despacho', 'description' => 'Zona de despacho'),
        array('key' => 'RR', 'value' => 'RR', 'description' => 'Zona de rr'),
        array('key' => 'storage', 'value' => 'Almacenamiento', 'description' => 'Persona encargada de almacenar'),  );
        //Delete the configs table
        DB::table('wms_settings')->delete();

        //Insert the data
        DB::table('wms_settings')->insert($configs);
    }
}
