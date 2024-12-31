from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
import cv2
import face_recognition
import hashlib
import os
from Crypto.Cipher import AES
from werkzeug.utils import secure_filename

app = Flask(__name__)
CORS(app)

UPLOAD_FOLDER = 'uploads'
ENCRYPTED_FOLDER = 'encrypted'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['ENCRYPTED_FOLDER'] = ENCRYPTED_FOLDER

# Ensure directories exist
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(ENCRYPTED_FOLDER, exist_ok=True)

face_encoding = None
face_hash = None
aes_key = None
BLOCK_SIZE = 16

# Function to pad data for AES encryption
def pad(data):
    padding_length = BLOCK_SIZE - len(data) % BLOCK_SIZE
    return data + (chr(padding_length) * padding_length).encode()

# Function to unpad data after AES decryption
def unpad(data):
    padding_length = data[-1]
    return data[:-padding_length]

@app.route('/upload-photo', methods=['POST'])
def upload_photo():
    global face_encoding

    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({"error": "No file selected"}), 400

    filename = secure_filename(file.filename)
    file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
    file.save(file_path)

    image = face_recognition.load_image_file(file_path)
    face_locations = face_recognition.face_locations(image)

    if len(face_locations) > 0:
        face_encoding = face_recognition.face_encodings(image, face_locations)[0]
        return jsonify({"image": file_path}), 200
    else:
        return jsonify({"error": "No face detected in the uploaded image"}), 400

@app.route('/capture-face', methods=['POST'])
def capture_face():
    global face_encoding

    video_capture = cv2.VideoCapture(0)
    face_detected = False

    while True:
        ret, frame = video_capture.read()
        rgb_frame = frame[:, :, ::-1]
        face_locations = face_recognition.face_locations(rgb_frame, model="cnn")

        if len(face_locations) > 0:
            face_encoding = face_recognition.face_encodings(rgb_frame, face_locations)[0]
            face_detected = True
            break

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    video_capture.release()
    cv2.destroyAllWindows()

    if face_detected:
        return jsonify({"success": True}), 200
    else:
        return jsonify({"error": "No face detected"}), 400

@app.route('/generate-hash', methods=['POST'])
def generate_hash():
    global face_encoding, face_hash, aes_key

    if face_encoding is None:
        return jsonify({"error": "No face encoding available"}), 400

    face_encoding_bytes = face_encoding.tobytes()
    hash_object = hashlib.sha256()
    hash_object.update(face_encoding_bytes)
    face_hash = hash_object.hexdigest()
    aes_key = hash_object.digest()[:32]

    return jsonify({"hash": face_hash}), 200

@app.route('/encrypt-file', methods=['POST'])
def encrypt_file():
    global aes_key

    if aes_key is None:
        return jsonify({"error": "Hash key not generated"}), 400

    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({"error": "No file selected"}), 400

    filename = secure_filename(file.filename)
    file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
    file.save(file_path)

    with open(file_path, 'rb') as f:
        data = f.read()

    cipher = AES.new(aes_key, AES.MODE_CBC)
    iv = cipher.iv
    encrypted_data = cipher.encrypt(pad(data))

    encrypted_file_path = os.path.join(app.config['ENCRYPTED_FOLDER'], f"{filename}.enc")
    with open(encrypted_file_path, 'wb') as f:
        f.write(iv + encrypted_data)

    return jsonify({"message": "File encrypted successfully", "file": encrypted_file_path}), 200

@app.route('/decrypt-file', methods=['POST'])
def decrypt_file():
    global aes_key

    if aes_key is None:
        return jsonify({"error": "Hash key not generated"}), 400

    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({"error": "No file selected"}), 400

    filename = secure_filename(file.filename)
    file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
    file.save(file_path)

    with open(file_path, 'rb') as f:
        iv = f.read(16)
        encrypted_data = f.read()

    cipher = AES.new(aes_key, AES.MODE_CBC, iv=iv)
    decrypted_data = unpad(cipher.decrypt(encrypted_data))

    decrypted_file_path = os.path.join(app.config['UPLOAD_FOLDER'], f"{filename}.dec")
    with open(decrypted_file_path, 'wb') as f:
        f.write(decrypted_data)

    return jsonify({"message": "File decrypted successfully", "file": decrypted_file_path}), 200

if __name__ == '__main__':
    app.run(debug=True)
