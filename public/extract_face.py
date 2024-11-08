import cv2
import sys

def extract_face(image_path, output_path):
    # Load the image
    image = cv2.imread(image_path)
    
    # Load the pre-trained face detection model
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
    
    # Convert the image to grayscale
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    
    # Detect faces
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))
    
    if len(faces) > 0:
        # Extract the first face (if any)
        (x, y, w, h) = faces[0]
        face = image[y:y+h, x:x+w]
        
        # Save the cropped face
        cv2.imwrite(output_path, face)
        print("Face extracted and saved to:", output_path)
    else:
        print("No face detected.")

if __name__ == '__main__':
    extract_face(sys.argv[1], sys.argv[2])
