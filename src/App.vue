<template>
  <div class="flex min-h-screen flex-col bg-[var(--color-body-bg)]">
    <header
      class="h-20 bg-[var(--color-surface)] shadow-[0_1px_2px_0_rgb(var(--shadow-card-rgb)/0.12)]"
    >
      <div class="mx-auto flex h-full w-full max-w-[1100px] items-center px-4 sm:px-6">
        <div class="flex items-center gap-3">
          <img
            class="h-auto w-[140px] max-w-none object-contain"
            src="/assets/logo.png"
            alt="Logo con dos cobayas"
          />
          <div class="flex flex-col">
            <h1 class="text-[34px] leading-9 font-semibold text-[var(--color-text-dark)]">
              PetWeight
            </h1>
            <p class="text-lg leading-5 text-[var(--color-text-secondary)]">
              Seguimiento de peso de mascotas
            </p>
          </div>
        </div>
      </div>
    </header>

    <main class="mx-auto w-full max-w-[1100px] flex-1 px-4 py-6 sm:px-6 sm:py-8">
      <section class="mx-auto flex w-full flex-col gap-6 sm:flex-row sm:justify-between">
        <ProfileCard
          name="Natty"
          photo="/assets/natty.jpg"
          back_photo="/assets/natty-avatar-white.jpg"
          breed="Americana"
          nickname="Niña peluche"
          birthday="29 septiembre 2025"
          primary_color="var(--color-primary-natty)"
          :current_weight="800"
          :weight_diff="25"
        />
        <ProfileCard
          name="Moka"
          photo="/assets/moka.jpg"
          back_photo="/assets/moka-avatar-white.jpg"
          breed="Abisinia"
          nickname="Despeluchá"
          birthday="29 septiembre 2025"
          primary_color="var(--color-primary-moka)"
          :current_weight="800"
          :weight_diff="25"
        />
      </section>

      <section class="mt-6 sm:mt-8">
        <DataView />
      </section>

      <section class="mt-6 sm:mt-8">
        <WeightForm />
      </section>
    </main>
  </div>
</template>

<script setup lang="js">
import DataView from '@/components/DataView.vue'
import ProfileCard from '@/components/ProfileCard.vue'
import WeightForm from '@/components/WeightForm.vue'
</script>
