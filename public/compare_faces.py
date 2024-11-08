import sys
import face_recognition

# Load the images
image1_path = sys.argv[1]
image2_path = sys.argv[2]

# Load the images into face_recognition
image1 = face_recognition.load_image_file(image1_path)
image2 = face_recognition.load_image_file(image2_path)

# Get face encodings for each image
encodings1 = face_recognition.face_encodings(image1)
encodings2 = face_recognition.face_encodings(image2)

# Ensure faces were found in both images
if len(encodings1) == 0 or len(encodings2) == 0:
    print("No face found in one of the images.")
    sys.exit(1)

# Compare the first face in each image
result = face_recognition.compare_faces([encodings1[0]], encodings2[0])

# Output the result
if result[0]:
    print("Match")
else:
    print("No Match")
