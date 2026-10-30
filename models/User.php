<?php
class User {
    private $db; public function __construct(PDO $db) { $this->db = $db; }
    public function findByEmail(string $email){ $s=$this->db->prepare('SELECT u.*, r.nama_role AS role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=? LIMIT 1'); $s->execute([$email]); return $s->fetch() ?: null; }
    public function find(int $id){ $s=$this->db->prepare('SELECT u.*, r.nama_role AS role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?'); $s->execute([$id]); return $s->fetch() ?: null; }
    public function all(int $limit=50,int $offset=0): array { $s=$this->db->prepare('SELECT u.id,u.nama,u.username,u.email,r.nama_role AS role,u.status,u.created_at FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC LIMIT ? OFFSET ?'); $s->bindValue(1,$limit,PDO::PARAM_INT);$s->bindValue(2,$offset,PDO::PARAM_INT);$s->execute();return $s->fetchAll(); }
    public function roles(): array { return $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll(); }
    public function exists(string $field,string $value,$except=null): bool { $sql="SELECT id FROM users WHERE {$field}=?"; $args=[$value]; if($except){$sql.=' AND id<>?';$args[]=$except;} $s=$this->db->prepare($sql);$s->execute($args);return (bool)$s->fetchColumn(); }
    public function create(array $d): int { $s=$this->db->prepare('INSERT INTO users(nama,username,email,password,role_id,status) VALUES(?,?,?,?,?,?)');$s->execute([$d['nama'],$d['username'],$d['email'],password_hash($d['password'],PASSWORD_DEFAULT),$d['role_id'],$d['status']]);return (int)$this->db->lastInsertId(); }
    public function update(int $id,array $d){ $sql='UPDATE users SET nama=?,username=?,email=?,role_id=?,status=?';$args=[$d['nama'],$d['username'],$d['email'],$d['role_id'],$d['status']];if(!empty($d['password'])){$sql.=',password=?';$args[]=password_hash($d['password'],PASSWORD_DEFAULT);} $sql.=' WHERE id=?';$args[]=$id;$s=$this->db->prepare($sql);$s->execute($args); }
    public function delete(int $id){ $s=$this->db->prepare('DELETE FROM users WHERE id=?');$s->execute([$id]); }
    public function count(): int { return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(); }
}
