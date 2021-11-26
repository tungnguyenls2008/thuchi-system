<?php

namespace App\Http\Controllers;

use App\Exports\CheckoutOrderExport;
use App\Http\Requests\CreateCheckoutOrderRequest;
use App\Http\Requests\UpdateCheckoutOrderRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\CheckoutOrder;
use App\models\ExcelSample;
use App\Models\Menu;
use App\Models\Note;
use App\Models\User;
use backend\models\Sample;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Response;

class CheckoutOrderController extends AppBaseController
{
    /**
     * Display a listing of the CheckoutOrder.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|Response
     */
    public function index(Request $request)
    {
        /** @var CheckoutOrder $checkoutOrders */
        $checkoutOrders = CheckoutOrder::OrderBy('created_at', 'desc')->paginate(15);


        return view('checkout_orders.index')
            ->with('checkoutOrders', $checkoutOrders);
    }
    public function search(Request $request)
    {
        $search=$request->post();

        $checkoutOrders= CheckoutOrder::OrderBy('created_at','desc');
        if (isset($search['bill_code'])){
            $checkoutOrders->where('bill_code','like','%'.$search['bill_code'].'%');
        }
        if (isset($search['customer_info'])){
            $checkoutOrders->where('customer_info','like','%'.$search['customer_info'].'%');
        }
        if (isset($search['user_id'])){
            $checkoutOrders->where(['user_id'=>$search['user_id']]);
        }
        if (isset($search['create_from'])|| isset($search['create_to'])){
            if (!isset($search['create_from'])){
                $search['create_from']='1970-01-01';
            }
            if (!isset($search['create_to'])){
                $search['create_to']='2100-12-31';
            }
            $from=date('Y-m-d H:i:s',strtotime($search['create_from'].' 00:00:00'));
            $to=date('Y-m-d H:i:s',strtotime($search['create_to'].' 23:23:59'));
            $checkoutOrders->whereBetween('created_at',[$from,$to]);
        }
        $checkoutOrders=$checkoutOrders->paginate(15);
        return view('checkout_orders.index')
            ->with('checkoutOrders', $checkoutOrders,)->with('count',$checkoutOrders->count());
    }

    /**
     * Show the form for creating a new CheckoutOrder.
     *
     * @return Response
     */
    public function create()
    {
        return view('checkout_orders.create');
    }

    /**
     * Store a newly created CheckoutOrder in storage.
     *
     * @param CreateCheckoutOrderRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|Response
     */
    public function store(CreateCheckoutOrderRequest $request)
    {
        $input = $request->all();
        $input['menu_id'] = json_encode($input['menu_id']);

        foreach ($input['price'] as $key => $item) {
            if ($item != 0) {
                $input['type'][$key] = 0;
            } else {
                $input['type'][$key] = 1;
            }
        }
        ksort($input['type']);
        $input['type'] = json_encode($input['type']);
        $input['quantity'] = json_encode($input['quantity']);
        $input['price'] = json_encode($input['price']);
        $input['user_id'] = Auth::id();
        $last_order = CheckoutOrder::latest()->first();
        if ($last_order == null) {
            $input['bill_code'] = idGenerator(1);
        } else {
            $input['bill_code'] = idGenerator($last_order->id + 1);
        }
        if (isset($input['note'])) {
            $new_note['bill_code'] = $input['bill_code'];
            $new_note['content'] = $input['note'];
            $note = Note::create($new_note);
        }

        $checkoutOrder = CheckoutOrder::create($input);
        $checkoutOrder = $checkoutOrder->toArray();
        $checkoutOrder['menu_id'] = json_decode($checkoutOrder['menu_id'], true);
        foreach ($checkoutOrder['menu_id'] as $key => $item) {
            $menu = Menu::find($item);
            $checkoutOrder['menu_id'][$key] = $menu->name;
        }
        $checkoutOrder['quantity'] = json_decode($checkoutOrder['quantity'], true);
        $checkoutOrder['price'] = json_decode($checkoutOrder['price'], true);
        $checkoutOrder['type'] = json_decode($checkoutOrder['type'], true);
        foreach ($checkoutOrder['type'] as $key => $item) {
            if ($item == 1) {
                $checkoutOrder['type'][$key] = 'KM';
            } else {
                $checkoutOrder['type'][$key] = 'TT';
            }
        }
        for ($i = 0; $i < count($checkoutOrder['price']); $i++) {
            $checkoutOrder['total'][$i] = $checkoutOrder['quantity'][$i] * $checkoutOrder['price'][$i];
        }
        $checkoutOrder['total_before_tax'] = array_sum($checkoutOrder['total']);
        $checkoutOrder['user_id'] = User::find($checkoutOrder['user_id'])->name;
        $checkoutOrder['discount_percent'] = $input['discount_percent'] . '%';
        $checkoutOrder['discount'] = $input['discount'];
        $checkoutOrder['total_to_pay'] = $input['total_to_pay'];
        $checkoutOrder['created_at'] = date('d-m-Y H:i', strtotime($checkoutOrder['created_at']));
        if ($checkoutOrder['customer_info'] == null) {
            $checkoutOrder['customer_info'] = 'Không có';
        }
        $bill=$this->printInvoice($checkoutOrder);
        $order_update=CheckoutOrder::find($checkoutOrder['id']);
        $order_update->bill_path='/files/'.$bill;
        if ($order_update->update()){
            Flash::success('Lưu hóa đơn thành công!');

            return redirect(route('checkoutOrders.index'));
        }

    }

    /**
     * Display the specified CheckoutOrder.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var CheckoutOrder $checkoutOrder */
        $checkoutOrder = CheckoutOrder::find($id);

        if (empty($checkoutOrder)) {
            Flash::error('Checkout Order not found');

            return redirect(route('checkoutOrders.index'));
        }

        return view('checkout_orders.show')->with('checkoutOrder', $checkoutOrder);
    }

    /**
     * Show the form for editing the specified CheckoutOrder.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
//        /** @var CheckoutOrder $checkoutOrder */
//        $checkoutOrder = CheckoutOrder::find($id);
//
//        if (empty($checkoutOrder)) {
//            Flash::error('Checkout Order not found');
//
//            return redirect(route('checkoutOrders.index'));
//        }
//
//        return view('checkout_orders.edit')->with('checkoutOrder', $checkoutOrder);
    }

    /**
     * Update the specified CheckoutOrder in storage.
     *
     * @param int $id
     * @param UpdateCheckoutOrderRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCheckoutOrderRequest $request)
    {
//        /** @var CheckoutOrder $checkoutOrder */
//        $checkoutOrder = CheckoutOrder::find($id);
//
//        if (empty($checkoutOrder)) {
//            Flash::error('Checkout Order not found');
//
//            return redirect(route('checkoutOrders.index'));
//        }
//
//        $checkoutOrder->fill($request->all());
//        $checkoutOrder->save();
//
//        Flash::success('Checkout Order updated successfully.');
//
//        return redirect(route('checkoutOrders.index'));
    }

    /**
     * Remove the specified CheckoutOrder from storage.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Exception
     *
     */
    public function destroy($id)
    {
        /** @var CheckoutOrder $checkoutOrder */
        $checkoutOrder = CheckoutOrder::find($id);

        if (empty($checkoutOrder)) {
            Flash::error('Checkout Order not found');

            return redirect(route('checkoutOrders.index'));
        }

        $checkoutOrder->delete();

        Flash::success('Checkout Order deleted successfully.');

        return redirect(route('checkoutOrders.index'));
    }

    public function getMenuPrice(Request $request)
    {
        $id = $request['id'];
        $menu = Menu::where(['id' => $id])->first();
        return json_encode($menu);
    }

    public function export()
    {
        return Excel::download(new CheckoutOrderExport(), 'bills.xlsx');
    }

    public function createNote(Request $request)
    {
        $input = $request->all();
        $note = Note::create($input);
        Flash::success('Tạo ghi chú thành công');
        $checkoutOrders = CheckoutOrder::OrderBy('created_at', 'desc')->paginate(15);


        return view('checkout_orders.index')
            ->with('checkoutOrders', $checkoutOrders);
    }

    public function updateNote(Request $request)
    {
        $input = $request->all();

        $note = Note::find($input['note-id']);
        $note->content = $input['content'];
        $note->update();
        Flash::success('Cập nhật ghi chú thành công');
        $checkoutOrders = CheckoutOrder::OrderBy('created_at', 'desc')->paginate(15);


        return view('checkout_orders.index')
            ->with('checkoutOrders', $checkoutOrders);
    }

    public function printInvoice($data)
    {
        $spreadsheet = IOFactory::load(public_path() . '/templates/bepMeNaInvoice.xlsx');
        $cell_to_dub = $this->findCellByValue($spreadsheet, '{menu_id}');
        $sheet = $spreadsheet->getActiveSheet();
        $src_row = (int)(filter_var($cell_to_dub, FILTER_SANITIZE_NUMBER_INT));

        $sheet->insertNewRowBefore($src_row, count($data['menu_id']));
        for ($i = 1; $i <= count($data['menu_id']);$i++) {
            $this->copyRange($sheet, 'A' . ($src_row + count($data['menu_id'])) . ':F' . ($src_row + count($data['menu_id'])), 'A' . ($src_row + count($data['menu_id'])- $i));

        }


        $this->replaceInSheet($spreadsheet, $data, '{bill_code}');
        $this->replaceInSheet($spreadsheet, $data, '{user_id}');
        $this->replaceInSheet($spreadsheet, $data, '{customer_info}');
        $this->replaceInSheet($spreadsheet, $data, '{total_before_tax}');
        $this->replaceInSheet($spreadsheet, $data, '{total_to_pay}');
        $this->replaceInSheet($spreadsheet, $data, '{discount}');
        $this->replaceInSheet($spreadsheet, $data, '{discount_percent}');
        $this->replaceInSheet($spreadsheet, $data, '{created_at}');
        foreach ($data['menu_id'] as $menu) {
            $this->replaceInSheet($spreadsheet, $menu, '{menu_id}');
        }
        foreach ($data['quantity'] as $quantity) {
            $this->replaceInSheet($spreadsheet, $quantity, '{quantity}');
        }
        foreach ($data['price'] as $price) {
            $this->replaceInSheet($spreadsheet, $price, '{price}');
        }
        foreach ($data['total'] as $total) {
            $this->replaceInSheet($spreadsheet, $total, '{total}');
        }

        $last_menu_id = $this->searchInSheet($spreadsheet, '{menu_id}');
        $last_quantity = $this->searchInSheet($spreadsheet, '{quantity}');
        $last_price = $this->searchInSheet($spreadsheet, '{price}');
        $last_total = $this->searchInSheet($spreadsheet, '{total}');
        $this->setMassBlankValue($sheet,[$last_menu_id,$last_price,$last_quantity,$last_total]);

        $helper = new ExcelSample();
        $file_name = $data['bill_code'] . '-' . date('d-m-Y') . '.xls';
        $helper->write($spreadsheet, $file_name);
        return $file_name;
    }

    private function replaceInSheet($spreadsheet, $data, $string)
    {
        $matched = $this->searchInSheet($spreadsheet, $string);
        $data_record = str_replace(['{', '}'], '', strtolower($string));
        if ($matched != []) {
            foreach ($matched as $key => $cell) {
                $sheet = $spreadsheet->getActiveSheet();

                if (isset($data[$data_record])) {
                    $sheet->setCellValue($matched[0], $data[$data_record]);
                } else {
                    $sheet->setCellValue($matched[0], $data);
                }


            }
        }
    }

    private function setMassBlankValue($sheet, array $cells)
    {
        foreach ($cells as $key => $cell) {
            if ($cell != null && $cell[0] != null) {
                $sheet->setCellValue($cell[0], '');
            }
        }
    }

    public static function isRowEmpty($row)
    {
        foreach ($row->getCellIterator() as $cell) {
            if ($cell->getValue()) {
                return false;
            }
        }

        return true;
    }

    private function searchInSheet($object, $value)
    {
        $foundInCells = [];
        foreach ($object->getWorksheetIterator() as $worksheet) {
            $ws = $worksheet->getTitle();
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() == $value) {
                        $foundInCells[] = $cell->getCoordinate();
                    }
                }
            }
        }
        return $foundInCells;
    }

    private function findCellByValue($objPHPExcel, $searchValue)
    {
        $foundInCells = '';
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() == $searchValue) {
                        $foundInCells = $cell->getCoordinate();
                    }
                }
            }
        }
        return $foundInCells;
    }

    private function copyRange(Worksheet $sheet, $srcRange, $dstCell)
    {
        // Validate source range. Examples: A2:A3, A2:AB2, A27:B100
        if (!preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $srcRange, $srcRangeMatch)) {
            // Wrong source range
            return;
        }
        // Validate destination cell. Examples: A2, AB3, A27
        if (!preg_match('/^([A-Z]+)(\d+)$/', $dstCell, $destCellMatch)) {
            // Wrong destination cell
            return;
        }

        $srcColumnStart = $srcRangeMatch[1];
        $srcRowStart = $srcRangeMatch[2];
        $srcColumnEnd = $srcRangeMatch[3];
        $srcRowEnd = $srcRangeMatch[4];

        $destColumnStart = $destCellMatch[1];
        $destRowStart = $destCellMatch[2];

        // For looping purposes we need to convert the indexes instead
        // Note: We need to subtract 1 since column are 0-based and not 1-based like this method acts.

        $srcColumnStart = Coordinate::columnIndexFromString($srcColumnStart) - 1;
        $srcColumnEnd = Coordinate::columnIndexFromString($srcColumnEnd) - 1;
        $destColumnStart = Coordinate::columnIndexFromString($destColumnStart) - 1;

        $rowCount = 0;
        for ($row = $srcRowStart; $row <= $srcRowEnd; $row++) {
            $colCount = 0;
            for ($col = $srcColumnStart; $col <= $srcColumnEnd; $col++) {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $style = $sheet->getStyleByColumnAndRow($col, $row);
                $dstCell = Coordinate::stringFromColumnIndex($destColumnStart + $colCount) . (string)($destRowStart + $rowCount);
                $sheet->setCellValue($dstCell, $cell->getValue());
                $sheet->duplicateStyle($style, $dstCell);

                // Set width of column, but only once per row
                if ($rowCount === 0) {
                    $w = $sheet->getColumnDimensionByColumn($col)->getWidth();
                    $sheet->getColumnDimensionByColumn($destColumnStart + $colCount)->setAutoSize(false);
                    $sheet->getColumnDimensionByColumn($destColumnStart + $colCount)->setWidth($w);
                }

                $colCount++;
            }

            $h = $sheet->getRowDimension($row)->getRowHeight();
            $sheet->getRowDimension($destRowStart + $rowCount)->setRowHeight($h);

            $rowCount++;
        }

        foreach ($sheet->getMergeCells() as $mergeCell) {
            $mc = explode(":", $mergeCell);
            $mergeColSrcStart = Coordinate::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[0])) - 1;
            $mergeColSrcEnd = Coordinate::columnIndexFromString(preg_replace("/[0-9]*/", "", $mc[1])) - 1;
            $mergeRowSrcStart = ((int)preg_replace("/[A-Z]*/", "", $mc[0]));
            $mergeRowSrcEnd = ((int)preg_replace("/[A-Z]*/", "", $mc[1]));

            $relativeColStart = $mergeColSrcStart - $srcColumnStart;
            $relativeColEnd = $mergeColSrcEnd - $srcColumnStart;
            $relativeRowStart = $mergeRowSrcStart - $srcRowStart;
            $relativeRowEnd = $mergeRowSrcEnd - $srcRowStart;

            if (0 <= $mergeRowSrcStart && $mergeRowSrcStart >= $srcRowStart && $mergeRowSrcEnd <= $srcRowEnd) {
                $targetColStart = Coordinate::stringFromColumnIndex($destColumnStart + $relativeColStart);
                $targetColEnd = Coordinate::stringFromColumnIndex($destColumnStart + $relativeColEnd);
                $targetRowStart = $destRowStart + $relativeRowStart;
                $targetRowEnd = $destRowStart + $relativeRowEnd;

                $merge = (string)$targetColStart . (string)($targetRowStart) . ":" . (string)$targetColEnd . (string)($targetRowEnd);
                //Merge target cells
                $sheet->mergeCells($merge);
            }
        }
    }
}
