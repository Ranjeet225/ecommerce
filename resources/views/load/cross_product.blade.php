@foreach ($crossProducts as $item)
    <option value="{{$item->id}}">{{$item->name}}</option>
@endforeach