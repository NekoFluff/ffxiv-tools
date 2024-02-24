<template>
    <div class="container mx-auto">
        <header class="py-6 m-auto">
            <h1>FFXIV Tools</h1>
        </header>
        <main>
            <div class="flex">
                <router-link to="/">Home</router-link> |
                <router-link to="/about">About</router-link>

                <router-view></router-view>
            </div>
        </main>
    </div>
</template>