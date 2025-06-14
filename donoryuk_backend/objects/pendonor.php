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
    public $hospital_id;
    public $preferred_time;
    public $email; // Pastikan properti email ada di sini

    public function __construct($db){
        $this->conn = $db;
    }

    function create(){
        // Query INSERT untuk menyertakan semua kolom baru
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
                    hospital_id=:hospital_id,
                    preferred_time=:preferred_time,
                    email=:email,                 -- Tambah parameter email
                    registration_date=NOW()";

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
        $this->hospital_id=htmlspecialchars(strip_tags($this->hospital_id));
        $this->preferred_time=htmlspecialchars(strip_tags($this->preferred_time));
        $this->email=htmlspecialchars(strip_tags($this->email)); // Sanitasi email

        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":birthdate", $this->birthdate);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":rhesus", $this->rhesus);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":last_donor_date", $this->last_donor_date);
        $stmt->bindParam(":hospital_id", $this->hospital_id, PDO::PARAM_INT);
        $stmt->bindParam(":preferred_time", $this->preferred_time);
        $stmt->bindParam(":email", $this->email); // Bind parameter email

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    function nikExists(){
        $query = "SELECT id FROM " . $this->table_name . " WHERE nik = ? LIMIT 0,1";
        $stmt = $this->conn->prepare( $query );
        $this->nik=htmlspecialchars(strip_tags($this->nik));
        $stmt->bindParam(1, $this->nik);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    function readAll(){
        // SELECT query untuk menyertakan semua kolom baru dan JOIN dengan hospitals
        $query = "SELECT
                    p.id, p.fullname, p.nik, p.birthdate, p.gender, p.blood_group, p.rhesus,
                    p.phone, p.address, p.last_donor_date, p.registration_date,
                    p.hospital_id, p.preferred_time, p.email,  -- Tambah email
                    h.name AS hospital_name
                FROM
                    " . $this->table_name . " p
                LEFT JOIN
                    hospitals h ON p.hospital_id = h.id
                ORDER BY
                    p.registration_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    function readOne(){
        // SELECT query untuk satu pendonor, menyertakan semua kolom baru
        $query = "SELECT
                    id, fullname, nik, birthdate, gender, blood_group, rhesus, phone, address,
                    last_donor_date, registration_date, hospital_id, preferred_time, email
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
            $this->hospital_id = $row['hospital_id'];
            $this->preferred_time = $row['preferred_time'];
            $this->email = $row['email']; // Isi properti email
            return true;
        }
        return false;
    }

    function update(){
        // UPDATE query untuk semua kolom baru
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
                    hospital_id=:hospital_id,
                    preferred_time=:preferred_time,
                    email=:email                  -- Tambah parameter email
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
        $this->hospital_id=htmlspecialchars(strip_tags($this->hospital_id));
        $this->preferred_time=htmlspecialchars(strip_tags($this->preferred_time));
        $this->email=htmlspecialchars(strip_tags($this->email)); // Sanitasi email

        $stmt->bindParam(":fullname", $this->fullname);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":birthdate", $this->birthdate);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":rhesus", $this->rhesus);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":last_donor_date", $this->last_donor_date);
        $stmt->bindParam(":hospital_id", $this->hospital_id, PDO::PARAM_INT);
        $stmt->bindParam(":preferred_time", $this->preferred_time);
        $stmt->bindParam(":email", $this->email); // Bind parameter email
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if($stmt->execute()){
            return ($stmt->rowCount() > 0);
        }
        return false;
    }

    function delete(){
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