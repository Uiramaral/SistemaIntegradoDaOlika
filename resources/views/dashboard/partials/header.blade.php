<header class="bg-white border-b p-4 flex justify-between items-center">
    <!-- Mobile Menu Button -->
    <button class="md:hidden text-gray-600 hover:text-gray-900" onclick="toggleSidebar()">
        <i class="fa fa-bars text-xl"></i>
    </button>
    
    <!-- Right Side - Notifications and User -->
    <div class="flex items-center gap-4">
        <!-- Notifications -->
        <div class="relative">
            <i class="fa fa-bell text-orange-500 text-xl cursor-pointer hover:text-orange-600"></i>
            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">3</span>
        </div>
        
        <!-- User Info -->
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                <i class="fa fa-user text-white text-sm"></i>
            </div>
            <div class="hidden md:block">
                <div class="font-semibold text-gray-900">Admin</div>
                <div class="text-xs text-gray-500">Administrador</div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar-mobile');
    sidebar.classList.toggle('active');
}
</script>

