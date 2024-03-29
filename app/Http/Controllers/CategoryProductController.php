<?php

namespace App\Http\Controllers;

use App\Repositories\Eloquent\RepositoryInterface;
use Illuminate\Http\Request;
use App\Repositories\CategoryProducts\CategoryProductRepositoryInterface;

class CategoryProductController extends Controller
{
    /**
     *
     * Tại đây ta gọi và sử dungj các Repository một cách đơn giản
     * Mỗi một phương thức gọi ta dùng 1 phương thức.
     *
     * */
    // Đây là biến trung gian để gọi đến Interface
    protected $CategoryRepository;

    // Phương thức khởi tạo để gọi đến interface, Tham số đầu vào chính là interface
    public function __construct(CategoryProductRepositoryInterface $repository)
    {
        $this->CategoryRepository = $repository;
    }

    /**
     * Tại đây ta xây dựng CURD một cách ngắn gọn như sau
     * index
     * show
     * update
     * delete
     * restore
     * */
    public function index()
    {
        $CategoryProducts = $this->CategoryRepository->getAll(30);
        return view('admin.CategoryProducts.index', ['CategoryProducts' => $CategoryProducts]);
    }

    public function show($id)
    {
        $CategoryProducts = $this->CategoryRepository->find($id);
        return $CategoryProducts;
    }

    public function getStore()
    {
        $Parent_id = $this->getParentID();
        return view('admin.CategoryProducts.create', ['Parent_id' => $Parent_id]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $CategoryProducts = $this->CategoryRepository->create($data);
        if ($CategoryProducts == true) {
            $this->index();
        } else {
            return redirect()->back()->with('thong_bao', 'Add new item failed');
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $CategoryProduct = $this->CategoryRepository->update($data, $id);
        if ($CategoryProduct == true) {
            return redirect()->back()->with('thong_bao', 'Update an item success!');
        } else {
            return redirect()->back()->with('thong_bao', 'Update an item failed!');
        }
    }

    public function destroy($id)
    {
        $CategoryProduct = $this->CategoryRepository->delete($id);
        if ($CategoryProduct == true) {
            return redirect('admin/Categories/CategoriesProduct')->with('thong_bao', 'Delete an item success!');
        } else {
            return redirect('admin/Categories/CategoriesProduct')->with('thong_bao', 'Delete an item failed');
        }
    }

    /**
     * Gọi đến các phương thức đặc biệt
     * Lấy Parent_id và id
     * Lấy Info
     * */
    public function getParentID()
    {
        $Parent_id = $this->CategoryRepository->getParent_id();
        return $Parent_id;
    }

    public function getInfo()
    {
        $getInfoCategoryProducts = $this->CategoryRepository->getInfo();
        return $getInfoCategoryProducts;
    }

    /**
     * Các phương thức mở rộng khác được viết ở đây
     * Phương thức getUpdate
     * */
    public function getUpdate($id)
    {
        $showCategoryProduct = $this->show($id);
        $Parent_id = $this->getParentID();
        return view('admin.CategoryProducts.update',
            ['CategoryProduct' => $showCategoryProduct, 'Parent_id' => $Parent_id]);
    }
}
