<?php
// donoryuk_backend/objects/pendonor.php
class Pendonor{

    private $conn;
    private $table_name = "pendonor";

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
    public $hospital_id;    // Tambah properti baru ini
    public $preferred_time; // Tambah properti baru ini

    public function __construct($db){
        $this->conn = $db;
    }

    function create(){
        // Update query INSERT untuk menyertakan hospital_id dan preferred_time
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
                    last_donor_date=:last_donor_date,
                    hospital_id=:hospital_id,     -- Tambah
                    preferred_time=:preferred_time, -- Tambah
                    registration_date=NOW()"; // Tambahkan registration_date jika belum ada di query asli Anda

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
        // Sanitasi properti baru
        $this->hospital_id=htmlspecialchars(strip_tags($this->hospital_id));
        $this->preferred_time=htmlspecialchars(strip_tags($this->preferred_time));


        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":birthdate", $this->birthdate);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":rhesus", $this->rhesus);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":last_donor_date", $this->last_donor_date);
        // Bind parameter properti baru
        $stmt->bindParam(":hospital_id", $this->hospital_id, PDO::PARAM_INT); // Bind sebagai INT
        $stmt->bindParam(":preferred_time", $this->preferred_time);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    function nikExists(){ /* ... (tidak ada perubahan di sini) ... */
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


    function readAll(){
        // Update query SELECT untuk menyertakan hospital_id dan preferred_time
        // Direkomendasikan: JOIN dengan tabel hospitals untuk mendapatkan nama rumah sakit
        $query = "SELECT
                    p.id, p.fullname, p.nik, p.birthdate, p.gender, p.blood_group, p.rhesus,
                    p.phone, p.address, p.last_donor_date, p.registration_date,
                    p.hospital_id, p.preferred_time,  -- Tambah
                    h.name AS hospital_name           -- Tambah JOIN ini jika ingin nama RS
                FROM
                    " . $this->table_name . " p
                LEFT JOIN
                    hospitals h ON p.hospital_id = h.id -- JOIN dengan tabel hospitals
                ORDER BY
                    p.registration_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Metode readOne, update, delete (tidak ada perubahan fungsional terkait fitur ini, tapi sertakan properti baru jika digunakan)
    function readOne(){
        $query = "SELECT
                    id, fullname, nik, birthdate, gender, blood_group, rhesus, phone, address, last_donor_date, registration_date,
                    hospital_id, preferred_time -- Tambah
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
            $this->hospital_id = $row['hospital_id']; // Tambah
            $this->preferred_time = $row['preferred_time']; // Tambah
            return true;
        }
        return false;
    }

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
                    last_donor_date=:last_donor_date,
                    hospital_id=:hospital_id,     -- Tambah
                    preferred_time=:preferred_time -- Tambah
                  WHERE
                    id = :id";

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
        $this->id=htmlspecialchars(strip_tags($this->id));
        // Sanitasi properti baru
        $this->hospital_id=htmlspecialchars(strip_tags($this->hospital_id));
        $this->preferred_time=htmlspecialchars(strip_tags($this->preferred_time));


        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":birthdate", $this->birthdate);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":rhesus", $this->rhesus);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":last_donor_date", $this->last_donor_date);
        // Bind parameter properti baru
        $stmt->bindParam(":hospital_id", $this->hospital_id, PDO::PARAM_INT);
        $stmt->bindParam(":preferred_time", $this->preferred_time);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if($stmt->execute()){
            return ($stmt->rowCount() > 0);
        }
        return false;
    }

    function delete(){ /* ... (tidak ada perubahan di sini) ... */
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if($stmt->execute()){
            return ($stmt->rowCount() > 0);
        }
        return false;
    }
}
?>