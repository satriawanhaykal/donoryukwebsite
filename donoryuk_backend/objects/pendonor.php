<?php
// donoryuk_backend/objects/pendonor.php
class Pendonor{

    // Koneksi database dan nama tabel
    private $conn;
    private $table_name = "pendonor";

    // Properti objek (sesuai dengan kolom di tabel pendonor)
    public $id;
    public $fullname;
    public $nik;
    public $birthdate;
    public $gender;
    public $blood_group;
    public $rhesus;
    public $phone;
    public $address;
    public $last_donor_date;
    public $registration_date;

    // Konstruktor dengan $db sebagai koneksi database
    public function __construct($db){
        $this->conn = $db;
    }

    // Metode untuk membuat record pendonor baru
    function create(){
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    fullname=:fullname,
                    nik=:nik,
                    birthdate=:birthdate,
                    gender=:gender,
                    blood_group=:blood_group,
                    rhesus=:rhesus,
                    phone=:phone,
                    address=:address,
                    last_donor_date=:last_donor_date";

        $stmt = $this->conn->prepare($query);

        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->nik=htmlspecialchars(strip_tags($this->nik));
        $this->birthdate=htmlspecialchars(strip_tags($this->birthdate));
        $this->gender=htmlspecialchars(strip_tags($this->gender));
        $this->blood_group=htmlspecialchars(strip_tags($this->blood_group));
        $this->rhesus=htmlspecialchars(strip_tags($this->rhesus));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->last_donor_date = ($this->last_donor_date === null || $this->last_donor_date === '') ? null : htmlspecialchars(strip_tags($this->last_donor_date));


        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":birthdate", $this->birthdate);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":rhesus", $this->rhesus);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":last_donor_date", $this->last_donor_date);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Metode untuk mengecek apakah NIK sudah ada
    function nikExists(){
        $query = "SELECT id
                FROM " . $this->table_name . "
                WHERE nik = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare( $query );

        $this->nik=htmlspecialchars(strip_tags($this->nik));
        $stmt->bindParam(1, $this->nik);

        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0){
            return true;
        }
        return false;
    }

    // Metode untuk membaca semua pendonor
    function readAll(){
        $query = "SELECT
                    id, fullname, nik, birthdate, gender, blood_group, rhesus, phone, address, last_donor_date, registration_date
                FROM
                    " . $this->table_name . "
                ORDER BY
                    registration_date DESC"; // Urutkan berdasarkan tanggal pendaftaran terbaru

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt; // Mengembalikan PDOStatement
    }

    // Metode untuk membaca satu pendonor berdasarkan ID (BARU DITAMBAHKAN)
    function readOne(){
        $query = "SELECT
                    id, fullname, nik, birthdate, gender, blood_group, rhesus, phone, address, last_donor_date, registration_date
                FROM
                    " . $this->table_name . "
                WHERE
                    id = :id
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->fullname = $row['fullname'];
            $this->nik = $row['nik'];
            $this->birthdate = $row['birthdate'];
            $this->gender = $row['gender'];
            $this->blood_group = $row['blood_group'];
            $this->rhesus = $row['rhesus'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->last_donor_date = $row['last_donor_date'];
            $this->registration_date = $row['registration_date'];
            return true;
        }
        return false;
    }

    // Metode untuk memperbarui data pendonor (BARU DITAMBAHKAN)
    function update(){
        $query = "UPDATE " . $this->table_name . "
                  SET
                    fullname=:fullname,
                    nik=:nik,
                    birthdate=:birthdate,
                    gender=:gender,
                    blood_group=:blood_group,
                    rhesus=:rhesus,
                    phone=:phone,
                    address=:address,
                    last_donor_date=:last_donor_date
                  WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->fullname=htmlspecialchars(strip_tags($this->fullname));
        $this->nik=htmlspecialchars(strip_tags($this->nik));
        $this->birthdate=htmlspecialchars(strip_tags($this->birthdate));
        $this->gender=htmlspecialchars(strip_tags($this->gender));
        $this->blood_group=htmlspecialchars(strip_tags($this->blood_group));
        $this->rhesus=htmlspecialchars(strip_tags($this->rhesus));
        $this->phone=htmlspecialchars(strip_tags($this->phone));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->last_donor_date = ($this->last_donor_date === null || $this->last_donor_date === '') ? null : htmlspecialchars(strip_tags($this->last_donor_date));
        $this->id=htmlspecialchars(strip_tags($this->id)); // ID juga perlu disanitasi

        // Bind parameter
        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":birthdate", $this->birthdate);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":rhesus", $this->rhesus);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":last_donor_date", $this->last_donor_date);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT); // Bind ID sebagai integer

        if($stmt->execute()){
            return ($stmt->rowCount() > 0); // Mengembalikan true jika ada baris yang terpengaruh
        }
        return false;
    }

    // Metode untuk menghapus pendonor (BARU DITAMBAHKAN)
    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id)); // Sanitize ID
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT); // Bind ID sebagai integer

        if($stmt->execute()){
            return ($stmt->rowCount() > 0); // Mengembalikan true jika ada baris yang terpengaruh
        }
        return false;
    }
}
?>