@if($errors->any())
    <h4>{{$errors->first()}}</h4>
@endif

<form action="{{ route('login') }}" method="post">

    {{ csrf_field() }}

    <input name="username" placeholder="Username">
    <input name="password" placeholder="Password">

    <button type="submit">Lets go</button>

</form>
