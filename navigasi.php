<nav class="navbar">
    <div class="menu-icon">
        <a href="#" onclick="openSlideMenu()"> Menu</a>
</div>
<ul class="navbar-nav">
       <li><a href="home.php">Home</a></li>
       <li><a href="siswa.php">Siswa</a></li>
       <li><a href="prodi.php">Prodi</a></li>
       <li class="logout">
              <a href="logout.php">
                  Logout ( <?php echo $_SESSION['user']; ?>)
             </a>
        </li>
    </ul>
</nav>

<div id="side-menu" class="side-nav">
          <a href="#" class="btn-close" onclick="closeSlideMenu()">&times;</a>
          <a href="home.php">Home</a>
          <a href="siswa.php">Siswa</a>
          <a href="prodi.php">Prodi</a>
          <a href="logout.php">Logout</a>
</div>