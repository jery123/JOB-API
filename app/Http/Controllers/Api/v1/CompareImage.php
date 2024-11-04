<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SapientPro\ImageComparator\ImageComparator;

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
    
}
