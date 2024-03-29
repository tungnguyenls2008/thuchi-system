<div class="table-responsive">
    <table class="table" id="providers-table">
        <thead>
        <tr>
            <th>Tên nhà cung cấp</th>
            <th>Số điện thoại</th>
            <th>Địa chỉ</th>
            <th>Số lần giao dịch</th>
            <th>Ghi chú</th>
            <th>Trạng thái</th>
            <th>Thời gian tạo</th>
            <th colspan="3">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($providers as $provider)
            <tr>
                <td>{{ $provider->name }}</td>
                <td>{{ $provider->phone }}</td>
                <td>{{ $provider->address }}</td>
                <td>{{ $provider->count }}</td>
                <td>{{ $provider->note }}</td>
                <td>
                    @switch($provider->status)
                        @case(0) <a href="{{route('provider-toggle-status',['id'=>$provider->id])}}"
                                    class="badge badge-success"
                                    onclick="return confirm('Bạn chắc chắn muốn đổi trạng thái nhà cung cấp này?')">Hoạt động</a>@break
                        @case(1) <a href="{{route('provider-toggle-status',['id'=>$provider->id])}}"
                                    class="badge badge-danger"
                                    onclick="return confirm('Bạn chắc chắn muốn đổi trạng thái nhà cung cấp này?')">Tạm ngừng</a>@break
                    @endswitch
                </td>
                <td>{{ $provider->created_at }}</td>
                <td width="120">
                    {!! Form::open(['route' => ['providers.destroy', $provider->id], 'method' => 'delete']) !!}
                    <div class='btn-group'>
                        <a href="{{ route('providers.show', [$provider->id]) }}" class='btn btn-default btn-xs'>
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('providers.edit', [$provider->id]) }}" class='btn btn-default btn-xs'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
