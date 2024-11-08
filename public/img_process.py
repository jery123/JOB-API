import cv2
import sys

# Read image path and output path from command line arguments
input_image_path = sys.argv[1]
output_image_path = sys.argv[2]

# Load the image
image = cv2.imread(input_image_path)

# Check if the image was loaded correctly
if image is None:
    print(f"Error: Could not load image at {input_image_path}")
    sys.exit(1)

# Load Haar cascade for face detection
face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
)

# Convert to grayscale for face detection
gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

# Detect faces in the image
faces = face_cascade.detectMultiScale(gray, 1.1, 4)

# If faces are detected, crop the first detected face
if len(faces) == 0:
    print("No faces detected.")
    sys.exit(1)

for x, y, w, h in faces:
    face = image[y : y + h, x : x + w]
    cv2.imwrite(output_image_path, face)
    break  # Exit after first face detection

print("Face extraction completed.")
