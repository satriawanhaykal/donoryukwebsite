<?php
class Hospital{

    private $conn;
    private $table_name = "hospitals"; // Nama tabel rumah sakit Anda

    public $id;
    public $name;
    public $address;
    public $phone;
    public $hours;
    public $latitude;
    public $longitude;
    // public $blood_stock_json; // Jika Anda menyimpan stok dalam kolom JSON

    public function __construct($db){
        $this->conn = $db;
    }

    // Metode untuk membaca semua rumah sakit
    function readAll(){
        $query = "SELECT id, name, address, phone, hours, latitude, longitude FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt; // Mengembalikan PDOStatement
    }

    // Metode untuk membaca satu rumah sakit berdasarkan ID
    function readOne(){
        $query = "SELECT id, name, address, phone, hours, latitude, longitude
                  FROM " . $this->table_name . "
                  WHERE id = :id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->address = $row['address'];
            $this->phone = $row['phone'];
            $this->hours = $row['hours'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            return true;
        }
        return false;
    }

    // Metode untuk mendapatkan stok darah (PENTING: Sesuaikan dengan tabel blood_stock Anda)
    function getBloodStock($hospitalId){
        $stock = [];
        $query = "SELECT blood_group, quantity FROM blood_stock WHERE hospital_id = :hospital_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hospital_id', $hospitalId, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stock[$row['blood_group']] = (int)$row['quantity']; // Pastikan quantity sebagai integer
        }

        // Jika Anda memiliki data dummy stok atau ingin memastikan semua golongan darah ada
        $default_stock = ['A' => 0, 'B' => 0, 'AB' => 0, 'O' => 0];
        return array_merge($default_stock, $stock);
    }

    // Metode untuk menambah rumah sakit baru
    function create(){
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    name=:name,
                    address=:address,
                    phone=:phone,
                    hours=:hours,
                    latitude=:latitude,
                    longitude=:longitude";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->hours=htmlspecialchars(strip_tags($this->hours));
        $this->latitude=htmlspecialchars(strip_tags($this->latitude));
        $this->longitude=htmlspecialchars(strip_tags($this->longitude));

        // Bind
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":hours", $this->hours);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);

        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId(); // Dapatkan ID yang baru dibuat
            return true;
        }
        return false;
    }

    // Metode untuk memperbarui rumah sakit
    function update(){
        $query = "UPDATE " . $this->table_name . "
                  SET
                    name=:name,
                    address=:address,
                    phone=:phone,
                    hours=:hours,
                    latitude=:latitude,
                    longitude=:longitude
                  WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->hours=htmlspecialchars(strip_tags($this->hours));
        $this->latitude=htmlspecialchars(strip_tags($this->latitude));
        $this->longitude=htmlspecialchars(strip_tags($this->longitude));
        $this->id=htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":hours", $this->hours);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if($stmt->execute()){
            // Perlu cek affected_rows untuk tahu apakah ada perubahan
            return ($stmt->rowCount() > 0);
        }
        return false;
    }

    // Metode untuk menghapus rumah sakit
    function delete(){
        // Hapus stok darah terkait terlebih dahulu (jika onDelete CASCADE tidak diatur di DB)
        $query_delete_stock = "DELETE FROM blood_stock WHERE hospital_id = :hospital_id";
        $stmt_delete_stock = $this->conn->prepare($query_delete_stock);
        $stmt_delete_stock->bindParam(':hospital_id', $this->id, PDO::PARAM_INT);
        $stmt_delete_stock->execute();

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if($stmt->execute()){
            return ($stmt->rowCount() > 0);
        }
        return false;
    }
}
?>