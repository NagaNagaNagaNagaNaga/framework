<?php

namespace Media\Driver\Drivers;
use Symfony\Component\Process\Process;

class SoxShell extends \Media\Driver\Driver {
	private $track;
	private $version;
	private $mime;
	private $extension;
	private $optons = array(
		"samplerate" => 48000, //-r
		"channels" => 2, //-c
		"bitdepth" => 16
	);
	public $background = false;
	static $supported;

	public function __construct($filename,$extension,$mime,$samplerate=48000,$channels=1,$bitrate=16) {
		$this->loadTrack($filename);
		$this->version = $this->getVersion();
		$this->mime = $mime;
		$this->extension = $extension;
		$this->options['samplerate'] = $samplerate;
		$this->options['channels'] = $channels;
		$this->options['bitdepth'] = $bitrate;
	}

	public static function supportedCodecs(&$formats) {
		if(!empty(self::$supported)) {
			return self::$supported;
		}
		$process = new Process('sox -h');
		$process->run();
		if(preg_match("/AUDIO FILE FORMATS: (.*)/",$process->getOutput(),$matches)) {
			$codecs = explode(" ",$matches[1]);
			foreach($codecs as $codec) {
				if(!in_array($codec,array('oga','ogg', 'aiff', 'flac'))) {
					continue;
				}
				$formats["in"][$codec] = $codec;
				$formats["out"][$codec] = $codec;
			}
		} else {
			$formats["in"]["ogg"] = "ogg";
			$formats["out"]["ogg"] = "ogg";
		}
		$formats["in"]["wav"] = "wav";
		$formats["in"]["oga"] = "oga";
		$formats["out"]["oga"] = "oga";

		$f = array("",12,16,24,32,44,96,192);
		foreach($f as $ff) {
			$formats["in"]["sln".$ff] = "sln".$ff;
			$formats["out"]["sln".$ff] = "sln".$ff;
		}
		self::$supported = $formats;
		return self::$supported;
	}

	public static function isCodecSupported($codec,$direction) {
		$formats = array();
		$formats = self::supportedCodecs($formats);
		return in_array($codec,$formats[$direction]);
	}

	public static function installed() {
		$process = new Process('sox --version');
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
		$process = new Process('sox --version');
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new \RuntimeException($process->getErrorOutput());
		}
		//sox: SoX v14.2.0
		if(preg_match("/v(.*)/",$process->getOutput(),$matches)) {
			return $matches[1];
		} else {
			throw new \Exception("Unable to parse version");
		}
	}

	public function convert($newFilename,$extension,$mime) {
		switch($extension) {
			case "wav":
				switch($this->extension) {
					case "sln":
						$process = new Process('sox -t raw -s -b 16 -r 8000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln12":
						$process = new Process('sox -t raw -s -b 16 -r 12000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln16":
						$process = new Process('sox -t raw -s -b 16 -r 16000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln24":
						$process = new Process('sox -t raw -s -b 16 -r 24000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln32":
						$process = new Process('sox -t raw -s -b 16 -r 32000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln44":
						$process = new Process('sox -t raw -s -b 16 -r 44000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln48":
						$process = new Process('sox -t raw -s -b 16 -r 48000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln96":
						$process = new Process('sox -t raw -s -b 16 -r 96000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					case "sln192":
						$process = new Process('sox -t raw -s -b 16 -r 192000 '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
					default:
						$process = new Process('sox '.$this->track.' -r '.$this->options['samplerate'].' -b '.$this->options['bitdepth'].' -c 1 '.$newFilename);
					break;
				}
			break;
			case "sln":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 8000 -c 1 '.$newFilename);
			break;
			case "sln12":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 12000 -c 1 '.$newFilename);
			break;
			case "sln16":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 16000 -c 1 '.$newFilename);
			break;
			case "sln24":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 24000 -c 1 '.$newFilename);
			break;
			case "sln32":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 32000 -c 1 '.$newFilename);
			break;
			case "sln44":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 44100 -c 1 '.$newFilename);
			break;
			case "sln48":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 48000 -c 1 '.$newFilename);
			break;
			case "sln96":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 96000 -c 1 '.$newFilename);
			break;
			case "sln192":
				$process = new Process('sox '.$this->track.' -t raw -b 16 -r 192000 -c 1 '.$newFilename);
			break;
			default:
				$process = new Process('sox '.$this->track.' -c 1 '.$newFilename);
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
