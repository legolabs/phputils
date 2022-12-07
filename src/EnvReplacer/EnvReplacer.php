<?php

namespace Legolabs\Utils;


class EnvReplacer
{
	function __construct(private string $path)
	{
	}


	/** Execute replaces in file */
	public function apply() : void
	{
		// Check file existence and permissions and get content
		$file_content = $this->get_file_content($this->path);

		// Continue only if file exists and has r/w permissions
		if ($file_content !== false)
		{
			// Get markers contained in file
			$markers = $this->get_markers($file_content);

			// Continue only if regexp replace doesn't fail
			if ($markers !== false)
			{
				// Replace markers with values contained in matching environment variables
				$replaced_content = $this->replace_markers($file_content, $markers);

				// Rewrite file
				$this->rewrite_file($this->path, $replaced_content);
			}
		}
	}


	/** Check file existence and permissions and get content */
	private function get_file_content(string $path) : string|false
	{
		// Existence
		if (! file_exists($path))
		{
			$this->show_warning("File not found: '{$path}', skipping");
			return false;
		}

		// Readability
		if (! is_readable($path))
		{
			$this->show_warning("File is not readable: '{$path}', skipping");
			return false;
		}

		// Writability
		if (! is_writable($path))
		{
			$this->show_warning("File is not writable: '{$path}', skipping");
			return false;
		}

		// Return file content
		return file_get_contents($path);
	}


	/** Get markers contained in file */
	private function get_markers(string $file_content) : array|false
	{
		// Regular expression matches for markers like __MARKER__
		$regexp = '/__[A-Z0-9_]{5,20}__/';

        // Get all markers matching regexp
		$res = preg_match_all($regexp, $file_content, $markers);

        // Invalid regexp
		if ($res === false)
		{
			$this->show_warning("Error on matching regular expression '{$regexp}', skipping file");
			return false;
		}

        // Return matching markers
		else
		{
			return $markers[0];
		}
	}


	/** Replace markers with values contained in matching environment variables */
	private function replace_markers($file_content, $markers) : string
	{
        // Replace each marker with corresponding environment variable value, if present
		foreach ($markers as $marker)
		{
            // Environment variable matches marker content, without delimitators. Example:
            // Marker --> __MARKER_EXAMPLE__ 
            // Env --> MARKER_EXAMPLE
			$env = str_replace('__', '', $marker);
			$valore = getenv($env);

            // Environment variable exists, replace marker with value
			if ($valore !== false)
			{
				$file_content = str_replace($marker, $valore, $file_content);
			}

            // Environment variable doesn't exist, skip it
			else
			{
				$this->show_warning("Environment variable '{$env}' not found, skipping marker");
			}
		}

        // Return replaced content
		return $file_content;
	}


	/** Rewrite file */
	private function rewrite_file($path, $new_content) : void
	{
		file_put_contents($path, $new_content);
	}


	/** Show a warning */
	private function show_warning(string $msg) : void
	{
		echo "[WARNING] {$msg}\n";
	}
}