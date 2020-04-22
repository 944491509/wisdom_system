<?php
use App\Utils\UI\Anchor;
use App\Utils\UI\Button;
?>
@extends('layouts.app')
@section('content')
  <div class="authManage" id="new-authManage-app" data-school="{{session('school.id')}}">
    <div class="authTitle">
      <span>权限管理</span>
      <el-button class="btn" type="primary" @click="isShowAddAuthDrawer = true" v-if="isShowPermission">添加权限组+</el-button>
      <el-button class="btn" type="primary" @click="isShowPermission = true" v-if="!isShowPermission">返回</el-button>
    </div>
    <div style="padding: 0 20px;" v-if="isShowPermission">
      <el-table
        :data="tableData"
        style="width: 100%">
        <el-table-column
          prop="id"
          label="序号"
        >
        </el-table-column>
        <el-table-column
          prop="name"
          label="权限组名称"
        >
        </el-table-column>
        <el-table-column
          prop="typeName"
          label="类型">
        </el-table-column>
        <el-table-column
          prop="description"
          label="权限描述"
        >
        </el-table-column>
        <el-table-column
          label="操作"
        >
          <template slot-scope="scope">
            <el-button @click="setPermission(scope.row.id)" type="primary" size="small">设置权限</el-button>
            <el-button @click="setCount(scope.row.type, scope.row.id, scope.row)" type="success" size="small">账号管理</el-button>
            <el-button @click="deleteAuth(scope.row.id)" type="danger" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div v-else>
      <el-table
        :data="permission"
        style="width: 95%; margin: 20px auto;"
        row-key="id"
        border
        :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
        <el-table-column
          prop="name"
          label="菜单"
          width="180">
        </el-table-column>
        <el-table-column
          prop="id"
          label="权限"
          >
          <template slot-scope="scope">
            <el-checkbox-group v-if="scope.row.permissions && scope.row.permissions.length" v-model="permissionsList">
              <el-checkbox v-for="item in scope.row.permissions" :label="item.id" >@{{item.name}}</el-checkbox>
            </el-checkbox-group>
          </template>
        </el-table-column>
      </el-table>
      <div class="btn-create">
        <el-button  @click="savePermission" type="primary">保存</el-button>
      </div>
    </div>
    <div>
      <el-drawer
        title="添加权限组"
        :visible.sync="isShowAddAuthDrawer"
        :wrapperClosable="false"
        custom-class="attendance-form-drawer"
        :before-close="handleClose">
        <el-form ref="ruleForm" class="attendance-form"  label-width="50px" v-model="formData">
          <el-form-item label="名称">
            <el-input v-model="role.name"></el-input>
          </el-form-item>
          <el-form-item label="类型">
            <el-select v-model="role.type" placeholder="请选择" style="width: 100%">
              <el-option
                v-for="item in options"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="描述">
            <el-input
              type="textarea"
              :rows="4"
              placeholder="请输入描述"
              v-model="role.description">
            </el-input>
          </el-form-item>

        </el-form>
        <div class="btn-create">
            <el-button  @click="addRole" type="primary">保存</el-button>
        </div>
      </el-drawer>
      <el-drawer
        title="招生就业处权限组"
        :visible.sync="isShowAuthGroupDrawer"
        :wrapperClosable="false"
        custom-class="attendance-form-drawer"
        :before-close="handleClose">
        <manager-ment ref="managerMent" :school_id="school_id" :scoped="scoped"  @close = "isShowAuthGroupDrawer = false" />
        
      </el-drawer>
    </div>
    
  </div>

  <style scoped lang="scss">
    .authManage {
      background-color: #fff;
      min-height: 600px;
    }
    .authTitle {
      height: 50px;
      border-bottom: 1px solid #ccc;
      line-height: 50px;
    }
    .authTitle > span {
      float: left;
      font-size: 18px;
      font-weight: 600;
      margin-left: 20px;
    }
    .btn {
      float: right;
      margin-right: 20px;
      margin-top: 4px
    }
    .btn-create {
      margin-top: 50px;
      text-align: center;
    }
    .attendance-form {
      padding-right: 20px;
      padding-left: 10px;
    }
  </style>
@endsection
