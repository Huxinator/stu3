<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpColonizeBuildingData extends BaseTable { #{{{

	const tablename = 'stu_rumps_colonize_building';
	protected $tablename = 'stu_rumps_colonize_building';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setRumpId($value) { # {{{
		$this->setFieldValue('rump_id',$value,'getRumpId');
	} # }}}

	/**
	 */
	public function getRumpId() { # {{{
		return $this->data['rump_id'];
	} # }}}

	/**
	 */
	public function setBuildingId($value) { # {{{
		$this->setFieldValue('building_id',$value,'getBuildingId');
	} # }}}

	/**
	 */
	public function getBuildingId() { # {{{
		return $this->data['building_id'];
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpColonizeBuilding extends RumpColonizeBuildingData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getByRump($rump_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE rump_id='.$rump_id.' LIMIT 1',4);
		if ($result == 0) {
			return FALSE;
		}
		return new RumpColonizeBuildingData($result);
	} # }}}

} #}}}

?>
