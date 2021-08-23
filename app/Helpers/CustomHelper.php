<?php 

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class CustomHelper
{
	public $disk = 'public';

	/**
	 * Handle logic to store file
	 */
	public function storeFile($fileDir, $file) 
	{
		$storagePath = Storage::disk($this->disk)->put($fileDir, $file);
		return $storagePath;
	}

	/**
	 * Handle logic to check file exists
	 */
	public function fileExists($file) 
	{
		return (Storage::disk($this->disk)->exists($file) == TRUE) ? TRUE : FALSE; 
	}

	/**
	 * Handle logic to delete file
	 */
	public function delete($file) 
	{
		return Storage::disk($this->disk)->delete($file);
	}

	/**
	 * Handle logic to retrive file
	 */
	public function getFileURL($file)
	{
		return Storage::disk($this->disk)->url($file);
	}

	/**
	 * Handle logic to getFilePath
	 */
	public function getFilePath($fileDir, $file)
	{
		$path = $fileDir."/".$file;
		return Storage::disk($this->disk)->url($path);
	}
}