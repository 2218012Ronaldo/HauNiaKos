@extends('layouts.app')

@section('content')
    <div id="Background"
        class="absolute top-0 h-[430px] w-full rounded-b-[75px] bg-[linear-gradient(180deg,#F2F9E6_0%,#D2EDE4_100%)]">
    </div>
    <div class="relative my-[60px] flex flex-col gap-[30px] px-5">
        <h1 class="text-center text-[30px] font-bold leading-[45px]">Explore Our<br>Beautiful Koskos</h1>
        <form action="{{ route('find-kos.results') }}"
            class="flex flex-col gap-6 rounded-[30px] border border-[#F1F2F6] bg-white p-5">
            <div id="InputContainer" class="flex flex-col gap-[18px]">
                <div class="flex w-full flex-col gap-2">
                    <p class="font-semibold">Name</p>
                    <label
                        class="flex w-full items-center gap-3 rounded-full bg-white p-[14px_20px] ring-1 ring-[#F1F2F6] transition-all duration-300 focus-within:ring-[#91BF77]">
                        <img src="assets/images/icons/note-favorite-grey.svg" class="flex h-5 w-5 shrink-0" alt="icon">
                        <input type="text" name="search" id=""
                            class="placeholder:text-ngekos-grey w-full appearance-none font-semibold outline-none placeholder:font-normal"
                            placeholder="Type the koskos name">
                    </label>
                </div>
                <div class="flex w-full flex-col gap-2">
                    <p class="font-semibold">Choose City</p>
                    <label
                        class="relative flex w-full items-center gap-2 rounded-full bg-white p-[14px_20px] ring-1 ring-[#F1F2F6] transition-all duration-300 focus-within:ring-[#91BF77]">
                        <img src="assets/images/icons/location.svg"
                            class="absolute left-5 top-1/2 flex h-5 w-5 shrink-0 -translate-y-1/2 transform" alt="icon">
                        <select name="city" id="" class="w-full appearance-none bg-white pl-8 outline-none">
                            <option value="" hidden>Select city</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->slug }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <img src="assets/images/icons/arrow-down.svg" class="h-5 w-5" alt="icon">
                    </label>
                </div>
                <div class="flex w-full flex-col gap-2">
                    <p class="font-semibold">Choose Category</p>
                    <label
                        class="relative flex w-full items-center gap-2 rounded-full bg-white p-[14px_20px] ring-1 ring-[#F1F2F6] transition-all duration-300 focus-within:ring-[#91BF77]">
                        <img src="assets/images/icons/location.svg"
                            class="absolute left-5 top-1/2 flex h-5 w-5 shrink-0 -translate-y-1/2 transform" alt="icon">
                        <select name="category" id="" class="w-full appearance-none bg-white pl-8 outline-none">
                            <option value="" hidden>Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}">{{ $category->name }}</option>
                            @endforeach
                            < </select>
                                <img src="assets/images/icons/arrow-down.svg" class="h-5 w-5" alt="icon">
                    </label>
                </div>
                <button type="submit"
                    class="bg-ngekos-orange flex w-full justify-center rounded-full p-[14px_20px] font-bold text-white">Explore
                    Now</button>
            </div>
        </form>
    </div>
    <div id="BottomNav" class="relative flex h-[138px] w-full shrink-0">
        <nav class="fixed bottom-5 z-10 w-full max-w-[640px] px-5">
            <div class="bg-ngekos-black grid h-fit grid-cols-4 justify-between rounded-[40px] px-5 py-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/global.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Discover</span>
                </a>
                <a href="{{ route('check-booking') }}" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/note-favorite.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Orders</span>
                </a>
                <a href="{{ route('find-kos') }}" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/search-status-green.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Find</span>
                </a>
                <a href="#" class="flex flex-col items-center gap-2 text-center">
                    <img src="assets/images/icons/24-support.svg" class="flex h-8 w-8 shrink-0" alt="icon">
                    <span class="text-sm font-semibold text-white">Help</span>
                </a>
            </div>
        </nav>
    </div>
    @include('includes.navigation')
@endsection