<?php
/**
 * @file
 * SEC_User Table Seeder
 *
 * All DecimaAccounting code is copyright by the original authors and released under the GNU Aferro General Public License version 3 (AGPLv3) or later.
 * See COPYRIGHT and LICENSE.
 */
namespace Vendor\DecimaModule\Module\Seeders;

use DB;//clases Seeders
use Vendor\DecimaModule\Module\Empleado;
use Vendor\DecimaModule\Module\ExperienciaLaboral;
use Vendor\DecimaModule\Module\Puesto;
use Illuminate\Database\Seeder;//es parte del framework siempre debe de ir

class EmpleadoTableSeeder extends Seeder {

	public function run()
	{
		/*clase puesto*/
		Puesto::create(array('nombre' => 'Programador senior'));
		Puesto::create(array('nombre' => 'Programador junior'));

		Empleado::create(array('nombre' => 'jose','apellido'=>'ramos','edad'=>24,'salario' =>300.56,'descripcion' =>'descripcion','puesto_id' =>1));

		ExperienciaLaboral::create(array('cargo' => 'analista Programador','descripcion'=>'ejemplo de descripcion','fecha_inicio'=>'2016-02-02','fecha_fin' =>'2016-04-25','empleado_id' =>'descripcion'));
		ExperienciaLaboral::create(array('cargo' => 'beta tester','descripcion'=>'ejemplo de descripcion','fecha_inicio'=>'2016-01-01','fecha_fin' =>'2016-03-30','empleado_id' =>1));
	}
}
