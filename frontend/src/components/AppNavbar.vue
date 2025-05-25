<template>
  <nav>
    <ul>
      <li><router-link to="/">Login</router-link></li>
      <li><router-link to="/student">Student</router-link></li>
      <li><router-link to="/lecturer">Lecturer</router-link></li>
      <li><router-link to="/advisor">Advisor</router-link></li>
      <li><router-link to="/admin">Admin</router-link></li>
    </ul>
  </nav>
</template>

<script setup>

</script>


<style scoped>
nav {
  background-color: #3a86ff;
  padding: 10px;
}
ul {
  list-style: none;
  display: flex;
  gap: 15px;
  margin: 0;
  padding: 0;
}
li {
}
a {
  color: white;
  text-decoration: none;
}
a.router-link-exact-active {
  font-weight: bold;
  border-bottom: 2px solid white;
}
</style>
