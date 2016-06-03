<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Counter;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Counter\Counter as Spec;

class Counter implements Spec {
	use ComponentHelper;

	private static $types = array
		( self::NOVELTY
		, self::STATUS
		);

	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	int
	 */
	private $number;

	/**
	 * @param string	$type
	 * @param int		$number
	 */
	public function __construct($type, $number) {
		$this->checkArgIsElement("type", $type, self::$types, "counter type");
		$this->checkIntArg("number", $number);
		$this->type = $type;
		$this->number = $number;
	}

	/**
	 * @inheritdoc
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @inheritdoc
	 */
	public function withType($type) {
		$this->checkArgIsElement("type", $type, self::$types, "counter type");
		$clone = clone $this;
		$clone->type = $type;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @inheritdoc
	 */
	public function withNumber($number) {
		$this->checkIntArg("number", $number);
		$clone = clone $this;
		$clone->number = $number;
		return $clone;
	}

	// Helper
	static protected function is_valid_type($type) {
		static $types = array
			( self::NOVELTY
			, self::STATUS
			);
		return in_array($type, $types);
	}

}
