<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SapientPro\ImageComparator\ImageComparator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class CompareImage extends Controller
{
    //compare two images
    public function compare1(Request $request){
         try {
            $image1 = 'dj1.jpg';
            $image2 = 'dj2.jpg';

            $imageComparator = new ImageComparator();

            $similarity = $imageComparator->compare($image1, $image2);

            return response()->json(['status' => true, 'similarity' => $similarity], 200);
         } catch (\Exception $er) {
            return response()->json(['status' => false, 'error' => $er->getMessage()], 500);
         }
    }

     public function extractFace(Request $request)
    {
        // Validate and store the uploaded image
        $request->validate(['id_card_image' => 'required|image|mimes:jpeg,png,jpg|max:2048']);
        $filePath = $request->file('id_card_image')->store('id_card_images');

        // Define paths for input and output images
        $inputImagePath = storage_path('app/' . $filePath);
        $outputImagePath = storage_path('app/faces/cropped_face.jpg');
         // Ensure output directory exists
         $outputDir = storage_path('app/faces');
         if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true); // Create the directory if it doesn't exist
         }
        // Run Python script to extract the face
        $pythonPath = 'C:\Users\GENERAL STORES\AppData\Local\Microsoft\WindowsApps\python.exe';
        $scriptPath = public_path('img_process.py'); // Full path to the Python script in public directory
        $process = new Process([$pythonPath, $scriptPath, $inputImagePath, $outputImagePath]);

      //   $process = new Process([$pythonPath, 'img_process.py', $inputImagePath, $outputImagePath]);
        $process->run();

        // Check if the Python script was successful
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return response()->download($outputImagePath, 'cropped_face.jpg');
    
   }
   public function uploadAndExtractFace(Request $request)
    {
      try {
         
        // Validate the image file
        $request->validate([
            'id_card' => 'required|image|mimes:jpg,jpeg,png|max:10240', // Max 10MB
        ]);
        
        // Store the uploaded image
        $image = $request->file('id_card');
        // Define the storage path
         $folderPath = public_path('uploads/id_cards');

         // Ensure the directory exists
         if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true); // Create the directory if it doesn't exist
         }

         // Set the image path and filename
         $imageName = time() . '-' . $image->getClientOriginalName();
         $imagePath = $folderPath . DIRECTORY_SEPARATOR . $imageName;

         // Move the uploaded file to the public path
         $image->move($folderPath, $imageName);

      //   $imagePath = $image->storeAs('uploads/id_cards', time() . '-' . $image->getClientOriginalName());

        // Define the output folder and file name for the extracted face
        $outputPath = public_path('faces/' . time() . '_face.jpg');
        
        // Run the Python script to extract the face
        //   $pythonPath = "C:\Users\GENERAL STORES\AppData\Local\Microsoft\WindowsApps\python.exe";
      //   $command = escapeshellcmd("python " . public_path('extract_face.py') . " " . public_path($imagePath) . " " . $outputPath);
      //   exec($command, $output, $result_code);
        // Define paths with quotes around each to handle spaces
      // $pythonPath = '"C:\\Users\\GENERAL STORES\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe"';
      $scriptPath = '"' . public_path('extract_face.py') . '"';
      $imagePath = '"' . ($imagePath) . '"';
      $outputPath = '"' . ($outputPath) . '"';

      // Build the command
      $command = "python" . ' ' . $scriptPath . ' ' . $imagePath . ' ' . $outputPath;

      // Execute the command
      exec($command, $output, $result_code);

         // $pythonPath = '"C:\Users\GENERAL STORES\AppData\Local\Microsoft\WindowsApps\python.exe"';
         // $scriptPath = public_path('extract_face.py');
         // $command = $pythonPath . ' "' . $scriptPath . '" "' . public_path($imagePath) . '" "' . $outputPath . '"';
         // exec($command, $output, $result_code);

        // Check if face extraction was successful
        if ($result_code === 0) {
            return response()->json(['message' => 'Face extracted successfully', 'face_path' => asset('faces/' . basename($outputPath))]);
        } else {
            return response()->json(['message' => 'Face extraction failed'], 500);
        }
      } catch (\Exception $er) {
         return response()->json([$er->getMessage()], 500);
      }
    }

    public function compareImg(Request $request){
      try {
         $pythonPath = '"C:\\Users\\GENERAL STORES\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe"';
         $scriptPath = public_path('compare_faces.py');
         $imagePath1 = public_path('uploads/id_cards/image1.jpg');
         $imagePath2 = public_path('uploads/id_cards/image2.jpg');

         // Build the command to execute the Python script
         $command = $pythonPath . ' ' . $scriptPath . ' "' . $imagePath1 . '" "' . $imagePath2 . '"';
         exec($command, $output, $result_code);

         // Check the output for match result
         if (!empty($output) && $output[0] == "Match") {
            return response()->json(['status' => 'success', 'message' => 'Faces match']);
         } else {
            return response()->json(['status' => 'fail', 'message' => 'Faces do not match']);
         }

      } catch (\Exception $er) {
         return response()->json([$er->getMessage()], 500);
      }

    }
    public function compareFaces(Request $request)
{
    // Validate the request
    $request->validate([
        'image1' => 'required|image|mimes:jpeg,png,jpg',
        'image2' => 'required|image|mimes:jpeg,png,jpg',
    ]);

    // Store the uploaded images in the public directory
    $folderPath = public_path('uploads/temp_faces');
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true); // Create the directory if it doesn't exist
    }

    // Save each uploaded file
    $image1Path = $folderPath . '/' . time() . '-image1.jpg';
    $image2Path = $folderPath . '/' . time() . '-image2.jpg';
    $request->file('image1')->move($folderPath, basename($image1Path));
    $request->file('image2')->move($folderPath, basename($image2Path));

    // Set the path to the Python executable and script
    $scriptPath = public_path('compare_faces.py');

    // Build the command to execute the Python script with both image paths
    $command = "python" . ' "' . $scriptPath . '" "' . $image1Path . '" "' . $image2Path . '"';

    // Execute the command
    exec($command, $output, $result_code);

    // Check the output from the Python script
    if (!empty($output) && $output[0] == "Match") {
        return response()->json(['status' => 'success', 'message' => 'Faces match']);
    } else {
        return response()->json(['status' => 'fail', 'message' => 'Faces do not match']);
    }
}
    
}
