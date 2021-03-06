<?php

namespace App\Http\Controllers\Upload;

use App\File;
use App\Upload;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller {

	/**
	 * UploadController constructor.
	 */
	public function __construct() {
		$this->middleware(['auth']);
	}


	/**
	 * Store a file(s) in database and on local storage.
	 * @param File $file
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(File $file, Request $request) {

	    // Make sure the user owns the file before we store it in database.
	    $this->authorize('touch', $file);

	    // Get the file(s)
	    $uploadedFile = $request->file('file');

	    // Call the "storeUpload" method defined below to
		// store the file(s) in database
		$upload = $this->storeUpload($file, $uploadedFile);

	    // Store the files on the default Laravel 'Storage' (on disk)
	    Storage::disk('local')->putFileAs(
	    	'files/' . $file->identifier,
		    $uploadedFile,
	        $upload->filename
	    );

	    return response()->json([
	    	'id' => $upload->id
	    ]);
    }


	/**
	 * Store a file in the database
	 * @param File $file
	 * @param UploadedFile $uploadedFile
	 *
	 * @return Upload
	 */
    protected function storeUpload(File $file, UploadedFile $uploadedFile) {

		// Make a new Upload model
    	$upload = new Upload;

    	// Fill the fields in the uploads table
	    $upload->fill([
    		'filename' => $this->generateFilename($uploadedFile),
		    'size' => $uploadedFile->getSize()
	    ]);

	    // Associate this upload with a file.
	    $upload->file()->associate($file);

	    // Associate this upload with a user
	    $upload->user()->associate(auth()->user());

	    // Save the file
	    $upload->save();

	    return $upload;
    }


	/**
	 * Generate a file name
	 * @param UploadedFile $uploadedFile
	 *
	 * @return null|string
	 */
    protected function generateFilename(UploadedFile $uploadedFile) {
	    return $uploadedFile->getClientOriginalName();
    }


	/**
	 * Delete the upload.
	 * @param File $file
	 * @param Upload $upload
	 */
    public function destroy(File $file, Upload $upload) {

	    // Make sure the user owns the file before we store it in database.
	    $this->authorize('touch', $file);

	    $this->authorize('touch', $upload);

	    // Prevent all files from being removed when we're editing a file
//	    if($file->uploads->count() === 1) {
//		    return response()->json(null, 422);
//	    }

	    $upload->delete();
    }
}
