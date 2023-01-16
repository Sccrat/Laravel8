<?php

namespace App\Common;
use App\Models\Setting;

/**
 * Custom class for confoguration settings stored in the table wms_settings
 */
class SettingsConstant
{

  private $settings = [
    ['key' => 'dc_size', 'value' => '2', 'description' => 'Tamaño códigos centro distribución'],
    ['key' => 'warehouse_size', 'value' => '3', 'description' => 'Tamaño códigos bodegas'],
    ['key' => 'zone_size', 'value' => '2', 'description' => 'Tamaño códigos zonas'],
    ['key' => 'module_size', 'value' => '2', 'description' => 'Tamaño códigos modulos'],
    ['key' => 'row_size', 'value' => '2', 'description' => 'Tamaño códigos filas'],
    ['key' => 'level_size', 'value' => '2', 'description' => 'Tamaño códigos nivel'],
    ['key' => 'receipt_group', 'value' => 'Etiqueteo y empaque', 'description' => 'Grupo de los empleados para recibir contenedores'],
    ['key' => 'stock_group', 'value' => 'Etiqueteo y empaque', 'description' => 'Grupo de los empleados para hacer inventario'],
    ['key' => 'receipt_machine', 'value' => 'Montacarga', 'description' => 'Tipo de máquina para recibir mercancia'],
    ['key' => 'stock_counts', 'value' => '3', 'description' => 'Cantidad de conteos para inventario'],
    ['key' => 'container_pallets', 'value' => '36', 'description' => 'Equivalencia estivas por contenedor'],
    ['key' => 'leader_charge', 'value' => 'Líder de bodega', 'description' => 'Cargo principal responsable de las tareas por bodega'],
    ['key' => 'picking_type', 'value' => 'Picking', 'description' => 'Nombre del tipo de zona asignado para el picking'],
    ['key' => 'concept_unavailable', 'value' => 'No existente', 'description' => 'Concepto de la posición no existente'],
    ['key' => 'feature_capacity', 'value' => 'Capacidad (kg)', 'description' => 'Caracteristica de capacidad'],
    ['key' => 'feature_weight', 'value' => 'Peso (kg)', 'description' => 'Caracteristica de peso'],
    ['key' => 'feature_height', 'value' => 'Alto (mts)', 'description' => 'Caracteristica de alto'],
    ['key' => 'feature_width', 'value' => 'Ancho (mts)', 'description' => 'Caracteristica de ancho'],
    ['key' => 'admin_role', 'value' => 'Administrador', 'description' => 'Rol de administración'],
    ['key' => 'use_code128', 'value' => 'true', 'description' => 'Indica si se debe manejar estándar 128 (valores true o false)']
  ];

  public function GetSettings()
  {
    return $this->settings;
  }

  public function InsertSettings($companyId)
  {
    foreach ($this->settings as $setting) {
      $setting['company_id'] = $companyId;
      Setting::firstOrCreate($setting);
    }
  }
}
