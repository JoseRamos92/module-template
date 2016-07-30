<?php
/**
 * @file
 * Currency Model.
 *
 * All DecimaERP code is copyright by the original authors and released under the GNU Aferro General Public License version 3 (AGPLv3) or later.
 * See COPYRIGHT and LICENSE.
 */
namespace Vendor\DecimaModule\Module;

use Eloquent;

class Puesto extends Eloquent{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'MOD_Puesto';

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = array('id');

	/**
	 * Indicates if the model should soft delete.
	 *
	 * @var bool
	 */
	//protected $softDelete = true;//eliminacion suave no elimina lo deja y pone la fecha "eliminada"
}
