<?php

require_once("src/Database/SQLConnect.PHP");
require_once("src/Sql/QueryBuilder.php");

use LionSql\Sql\QueryBuilder as Builder;

Builder::connectDatabase([
	'host' => 'mysql:host=localhost;dbname=lion_framework_db;charset=utf8',
	'user' => 'root',
	'password' => ''
]);

// Delete
$list = Builder::delete('menu', 'idmenu', [
	9, 'int'
]);
var_dump($list);

// Update
// $list = Builder::update('menu', 'menu_name,menu_icon,menu_type,menu_url,idstate,menu_code:idmenu', [
// 	['new example(true)', 'str'],
// 	[null, 'str'],
// 	['ITEM', 'str'],
// 	[null, 'str'],
// 	[1, 'int'],
// 	['AAA', 'str'],
// 	[7, 'int']
// ]);
// var_dump($list);

// Insert
// $list = Builder::insert('menu', 'menu_name,menu_icon,menu_type,menu_url,idstate,menu_code');

// $list = Builder::insert('menu', 'menu_name,menu_icon,menu_type,menu_url,idstate,menu_code', [
// 	['PRUEBAS99', 'str'],
// 	[null, 'str'],
// 	['ITEM', 'str'],
// 	[null, 'str'],
// 	[1, 'int'],
// 	['EFE12345xx61478', 'str']
// ]);

// var_dump($list);

// Select
// $sql = Builder::select('permissions', 'pms', 'pms.idpermissions:mn.menu_name:mn.menu_type:mn.menu_code:tp.type_permissions_name', [
// 	Builder::join('INNER', 'menu', 'mn', "pms.idmenu=mn.idmenu"),
// 	Builder::join('LEFT', 'type_permissions', 'tp', "pms.idtype_permissions=tp.idtype_permissions"),
// 	Builder::where('pms.idpermissions'),
// 	Builder::between()
// ]);

// $list = Builder::fetchAll(
// 	Builder::bindValue(Builder::prepare($sql), [
// 		[1, 'int'],
// 		[10, 'int']
// 	])
// );

// foreach ($list as $key => $item) {
// 	echo("{$item['idpermissions']}, {$item['menu_name']}, {$item['menu_type']}, {$item['menu_code']}, {$item['type_permissions_name']} <br>");
// }