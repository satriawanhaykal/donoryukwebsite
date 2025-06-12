<?php
// donoryuk_backend/objects/permintaan_darah.php
class PermintaanDarah{

    private $conn;
    private $table_name = "permintaan_darah";

    public $id;
    public $hospital_id;
    public $user_name;
    public $user_address;
    public $user_age;
    public $blood_group;
    public $quantity_taken;
    public $transaction_id;
    public $status;
    public $request_date;
    public $hospital_name; // Properti tambahan untuk join

    public function __construct($db){
        $this->conn = $db;
    }

    function create(){
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    hospital_id=:hospital_id,
                    user_name=:user_name,
                    user_address=:user_address,
                    user_age=:user_age,
                    blood_group=:blood_group,
                    quantity_taken=:quantity_taken,
                    transaction_id=:transaction_id";

        $stmt = $this->conn->prepare($query);

        $this->hospital_id = htmlspecialchars(strip_tags($this->hospital_id));
        $this->user_name = htmlspecialchars(strip_tags($this->user_name));
        $this->user_address = htmlspecialchars(strip_tags($this->user_address));
        $this->user_age = htmlspecialchars(strip_tags($this->user_age));
        $this->blood_group = htmlspecialchars(strip_tags($this->blood_group));
        $this->quantity_taken = htmlspecialchars(strip_tags($this->quantity_taken));
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));

        $stmt->bindParam(":hospital_id", $this->hospital_id);
        $stmt->bindParam(":user_name", $this->user_name);
        $stmt->bindParam(":user_address", $this->user_address);
        $stmt->bindParam(":user_age", $this->user_age);
        $stmt->bindParam(":blood_group", $this->blood_group);
        $stmt->bindParam(":quantity_taken", $this->quantity_taken);
        $stmt->bindParam(":transaction_id", $this->transaction_id);

        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function readOneByTransactionId(){
        $query = "SELECT pd.*, rs.name as hospital_name, rs.address as hospital_address, rs.phone as hospital_phone, rs.hours as hospital_hours, rs.latitude, rs.longitude
                  FROM " . $this->table_name . " pd
                  LEFT JOIN hospitals rs ON pd.hospital_id = rs.id
                  WHERE pd.transaction_id = :transaction_id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));
        $stmt->bindParam(':transaction_id', $this->transaction_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->hospital_id = $row['hospital_id'];
            $this->user_name = $row['user_name'];
            $this->user_address = $row['user_address'];
            $this->user_age = $row['user_age'];
            $this->blood_group = $row['blood_group'];
            $this->quantity_taken = $row['quantity_taken'];
            $this->transaction_id = $row['transaction_id'];
            $this->status = $row['status'];
            $this->request_date = $row['request_date'];
            $this->hospital_name = $row['hospital_name'];
            // Tambahan dari join
            $this->hospital_address = $row['hospital_address'];
            $this->hospital_phone = $row['hospital_phone'];
            $this->hospital_hours = $row['hospital_hours'];
            $this->latitude = $row['latitude'];
            $this->longitude = $row['longitude'];
            return true;
        }
        return false;
    }

    // Metode untuk membaca semua permintaan darah (BARU DITAMBAHKAN)
    function readAll(){
        $query = "SELECT pd.id, pd.hospital_id, pd.user_name, pd.user_address, pd.user_age, pd.blood_group, pd.quantity_taken, pd.transaction_id, pd.status, pd.request_date,
                         rs.name as hospital_name
                  FROM " . $this->table_name . " pd
                  LEFT JOIN hospitals rs ON pd.hospital_id = rs.id
                  ORDER BY pd.request_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Metode untuk mengupdate status permintaan darah (Opsional, jika Anda ingin admin mengubah status)
    function updateStatus(){
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        if($stmt->execute()){
            return ($stmt->rowCount() > 0);
        }
        return false;
    }
}
?>