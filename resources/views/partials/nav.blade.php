<div class="border-b border-gray-300 dark:border-slate-700  bg-opacity-90 dark:border-opacity-20  z-10 ">
    <div class="max-w-6xl mx-auto px-6 "  x-data="{ pages: false }">
        <nav class="-mb-px flex space-x-4 text-sm leading-5 overflow-x-auto scrollbar-hide text-gray-500">
            <div class="  py-3 px-2 rounded @if($nav == 'dashboard' ) border-b-2   border-gray-900 dark:border-slate-600 @endif ">
                <a href="{{url('/dashboard')}}" class=" px-0.5  @if($nav == 'dashboard' )   text-newgray-900 dark:text-gray-300 @endif   hover:text-newgray-900 dark:hover:text-gray-300 cursor-pointer" >dashboard</a>
            </div>


                <div class=" dark:hover:bg-newgray-700 py-3 px-2 rounded @if($nav == 'alerts' )border-b-2   border-gray-900 dark:border-slate-600 @endif ">
                    <a href="{{url('/alerts')}}" class=" px-0.5  @if($nav == 'alerts' )   text-newgray-900 dark:text-gray-300 @endif   hover:text-newgray-900 dark:hover:text-gray-300 cursor-pointer" >alerts</a>
                </div>
                {{-- @if (in_array(session('role_id'), [0, 1]))
                <div class=" dark:hover:bg-newgray-700 py-3 px-2 rounded @if($nav == 'alerts-test' )border-b-2   border-gray-900 dark:border-slate-600 @endif ">
                        <a href="{{url('/alerts-test')}}" class=" px-0.5  @if($nav == 'alerts-test' )   text-newgray-900 dark:text-gray-300 @endif   hover:text-newgray-900 dark:hover:text-gray-300 cursor-pointer" >alert-test</a>
                </div>
                @endif --}}
                @if (session('role_id') == 0)
                    <div class=" dark:hover:bg-newgray-700 py-3 px-2 rounded @if($nav == 'users' )border-b-2   border-gray-900 dark:border-slate-600 @endif ">
                        <a href="{{url('/users')}}" class=" px-0.5  @if($nav == 'users' )   text-newgray-900 dark:text-gray-300 @endif   hover:text-newgray-900 dark:hover:text-gray-300 cursor-pointer" >users</a>
                    </div>

                @endif
                <div class=" dark:hover:bg-newgray-700 py-3 px-2 rounded @if($nav == 'settings' )border-b-2   border-gray-900 dark:border-slate-600 @endif ">
                    <a href="{{url('/settings')}}" class=" px-0.5  @if($nav == 'settings' )   text-newgray-900 dark:text-gray-300 @endif   hover:text-newgray-900 dark:hover:text-gray-300 cursor-pointer" >settings</a>
                </div>


        </nav>
    </div>
</div>
