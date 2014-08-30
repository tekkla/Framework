<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Class for function about file input/output
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class FileIO
{
	/**
	 * Creates a directory by given path.
	 * If the directory exitsts the return
	 * value will be the given path. If not, the path is created and the return
	 * value boolean false/true
	 * @param string $path Path and name of dir
	 * @return string bool
	 */
	public static function createDir($path)
	{
		// does userdir exist? use it?
		if (file_exists($path))
			$result = $path;
			// Not? create path
		else
			$result = mkdir($path);

		return $result;
	}

	/**
	 * Deletes a given dir and recursive all files and folders within it.
	 * @param $dirname Path to the dir
	 * @return boolean
	 */
	public static function deleteDir($dirname)
	{
		if (is_dir($dirname))
			$dir_handle = opendir($dirname);

		if (!$dir_handle)
			return false;

		while ( ($file = readdir($dir_handle)) != false )
		{
			if ($file != "." && $file != "..")
			{
				if (!is_dir($dirname . "/" . $file))
					unlink($dirname . "/" . $file);
				else
					self::deleteDir($dirname . '/' . $file);
			}
		}

		closedir($dir_handle);
		rmdir($dirname);
		return true;
	}

	/**
	 * Moves the source to the destination.
	 * Both parameter have be to full paths.
	 * On success the return value will be the destination path. Otherwise it will
	 * be boolean false.
	 * @param string $source Path to source file
	 * @param string $destination Path to destination file
	 * @return string boolean
	 */
	public static function moveFile($source, $destination)
	{
		$source = BOARDDIR . $source;
		$destination = BOARDDIR . $destination;

		if (copy($source, $destination))
		{
			unlink($source);
			return $destination;
		}
		else
			return false;
	}

	/**
	 * Same as php's core move_uploaded_file extended with destination file exists
	 * check.
	 * Fails this check an error exception is throwm.
	 * @param string $source
	 * @param string $destination
	 * @param bool $check_exists
	 * @throws Error
	 */
	public static function moveUploadedFile($source, $destination, $check_exists = true)
	{
		if ($check_exists == true && self::exists($destination))
			Throw new Error('File already exits', 2001, array($destination));

		return move_uploaded_file($source, $destination);
	}

	/**
	 * Wrapper method for file_exists() which throws an error.
	 * @param unknown $path
	 * @param string $log_missing
	 * @throws Error
	 * @return boolean
	 */
	public static function exists($path, $log_missing = false)
	{
		$exists = file_exists($path);

		if (!$exists && $log_missing == true)
			Throw new Error('File not found.', 2000, array($path));

		return $exists;
	}

	/**
	 * Converts a filesize (bytes as integer) into a human readable string format.
	 * For example: 1024 => 1 KByte
	 * @param int $bytes
	 * @return string unknown
	 */
	public static function convFilesize($bytes)
	{
		if (!$bytes == '0'.$bytes)
			Throw new Error('Wrong parameter type', array($bytes, 'int'));

		if ($bytes > 0)
		{
			$unit = intval(log($bytes, 1024));
			$units = array(
				'Bytes',
				'KByte',
				'MByte',
				'GByte'
			);

			if (array_key_exists($unit, $units) === true)
				return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
		}

		return $bytes;
	}

	/**
	 * Cleans up a filename string from all characters which can make trouble on filesystems.
	 * @param string $name The string to cleanup
	 * @param string $delimiter
	 * @return string
	 */
	public static function cleanFilename($name, $delimiter = '-')
	{
		// The fileextension should not be normalized.
		if (strrpos($name, '.') !== false)
			list($name, $extension) = explode('.', $name);

		$name = String::normalizeString($name);
		$name = preg_replace('/[^[:alnum:]\-]+/', $delimiter, $name);
		$name = preg_replace('/' . $delimiter . '+/', $delimiter, $name);
		$name = rtrim($name, $delimiter);

		$cleaned = isset($extension) ? $name . '.' . $extension : $name;

		return $cleaned;
	}

	/**
	 * Returns an array of files inside the given directory path.
	 * @param string $path Directory path to get filelist from
	 * @throws PathError
	 * @return void multitype:string
	 */
	public static function getFilenamesFromDir($path)
	{
		// Add trailing slash if missing
		if (substr($path, -1) != '/')
			$path .= '/';

			// Output array for filenames
		$filenames = array();

		// Get dir handle
		$handle = opendir($path);

		// No handle, error exception
		if ($handle === false)
		{
			Throw new Error('File not found.', 2000, array($path));
			return;
		}

		while ( ($file = readdir($handle)) !== false )
		{
			// no '.' or '..' or dir
			if ('.' == $file || '..' == $file || is_dir($path . $file))
				continue;

				// store filename
			$filenames[] = $file;
		}

		closedir($handle);

		return $filenames;
	}

	/**
	 * Returns uploads done with WebExt
	 * @return array
	 */
	public static function getUploads()
	{
		return $_FILES['web_files'];
	}

	/**
	 * Transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
	 * @var $size string Size inas string (like '2M')
	 * @return int Size in bytes
	 */
	public static function convertPHPSizeToBytes($size)
	{
		$suffix = substr($size, -1);
		$value = substr($size, 0, -1);

		switch (strtoupper($suffix))
		{
			case 'P' :
				$value *= 1024;
			case 'T' :
				$value *= 1024;
			case 'G' :
				$value *= 1024;
			case 'M' :
				$value *= 1024;
			case 'K' :
				$value *= 1024;
				break;
		}

		return $value;
	}

	/**
	 * Returns the maximum size for uploads in bytes.
	 * @return int
	 */
	public static function getMaximumFileUploadSize()
	{
		return self::convertPHPSizeToBytes(ini_get('upload_max_filesize'));
	}
}
?>
