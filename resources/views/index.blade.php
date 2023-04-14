@extends('layouts.app')
@section('title', 'Diff index')

@section('content')
    <form method="post" action="{{ route('diff.createpost',['config'=>$config]) }}">
        @csrf
        <input type="hidden" name="config" value="{{ $config }}" />

        <table class="border">
        @foreach($config_data['urls'] as $index => $urlinfo)
            @php
                $url = $urlinfo['url'];
                $indexcode = $url.'--'.$urlinfo['width'];
            @endphp
            <tr>
                <td>{{ $url }} - {{ $urlinfo['width'] }}</td>
                @foreach($config_data['domains'] as $code => $domain)
                    <td><a href="{{ $domain }}{{ $url }}" target="_blank">{{ $code }}</a></td>
                @endforeach
                <td><a href="{{ $config_run[$indexcode]['diffimage'] }}" target="_blank"><img src="{{ $config_run[$indexcode]['diffimage_thumb'] }}" alt=""/></a></td>
                <td><a href="{{ route('diff.compare',['config'=>$config, 'index'=> $index]) }}">Compare</a></td>
                <td>{{ $config_run[$indexcode]['diff_score'] }}</td>
                <td>{{ Carbon\Carbon::createFromTimestamp($config_run[$indexcode]['lastrun'])->setTimezone('Europe/Brussels')->toDateTimeString() }}</td>
                <td><a href="{{ route('diff.create',['config'=>$config, 'index'=> $index]) }}">Redo</a></td>
                <td><input type="checkbox" name="indexes[]" value="{{ $index }}" /></td>
            </tr>
        @endforeach
            <tr><td colspan="8"></td><td><input type="submit" value="Redo" /></td></tr>
        </table>
    </form>
    <form method="post" action="{{ route('diff.add',['config'=>$config]) }}">
        @csrf
        <input type="hidden" name="config" value="{{ $config }}" />
        <table>
            <tr>
                <td>Url</td>
                <td><input type="text" name="newurl"></td>
            </tr>
            <tr>
                <td>Width</td>
                <td><input type="number" name="width" value="1920"></td>
            </tr>
        </table>
        <div><input type="submit" value="Add new url" /></div>
    </form>
@endsection