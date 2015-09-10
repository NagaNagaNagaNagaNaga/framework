<?php

namespace Media\Driver\Drivers;
use Symfony\Component\Process\Process;

class LameShell extends \Media\Driver\Driver {
	private $track;
	private $version;
	private $mime;
	private $extension;
	private $optons = array(
		"bitrate" => 128, //-r
		"samplerate" => 48000 //--resample
	);
	public $background = false;

	public function __construct($filename,$extension,$mime,$samplerate=48000,$channels=1,$bitrate=16) {
		$this->loadTrack($filename);
		$this->version = $this->getVersion();
		$this->mime = $mime;
		$this->extension = $extension;
		$this->options['samplerate'] = $samplerate;
	}

	public static function supportedCodecs(&$formats) {
		$formats["out"]["mp3"] = "mp3";
		return $formats;
	}

	public static function isCodecSupported($codec,$direction) {
		return $direction == "out" && in_array($codec,array("mp3"));
	}

	public static function installed() {
		$process = new Process('lame --version');
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			return false;
		}
		return true;
	}

	public function loadTrack($track) {
		if(empty($track)) {
			throw new \Exception("A track must be supplied");
		}
		if(!file_exists($track)) {
			throw new \Exception("Track [$track] not found");
		}
		if(!is_readable($track)) {
			throw new \Exception("Track [$track] not readable");
		}
		$this->track = $track;
	}

	public function getVersion() {
		$process = new Process('lame --version');
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new \RuntimeException($process->getErrorOutput());
		}
		//LAME 32bits version 3.99.5 (http://lame.sf.net)
		if(preg_match("/version (.*) \(/",$process->getOutput(),$matches)) {
			return $matches[1];
		} else {
			throw new \Exception("Unable to parse version");
		}
	}

	public function convert($newFilename,$extension,$mime) {
		switch($extension) {
			case "mp3":
				$process = new Process('lame -V3 '.$this->track.' '.$newFilename);
			break;
			default:
				throw new \Exception("Invalid type of $extension sent to LAME");
			break;
		}
		if(!$this->background) {
			$process->run();
			if (!$process->isSuccessful()) {
				throw new \RuntimeException($process->getErrorOutput());
			}
		} else {
			$process->start();
			if (!$process->isRunning()) {
				throw new \RuntimeException($process->getErrorOutput());
			}
		}
	}
}
