<?php
namespace GoatCore\Interfaces;

interface IDbAssets {

    public function one($id);

    public function create($data);

    public function update($id, $data);

    public function delete($id);

    public function find($filter);

    public function list();
}