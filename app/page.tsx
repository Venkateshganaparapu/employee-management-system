"use client";

import { motion } from "framer-motion";
import Link from "next/link";
import Image from "next/image";
import {
  ArrowRight,
  Shield,
  Clock,
  Star,
  Zap,
  ChevronDown,
  Car,
  MapPin,
} from "lucide-react";
import { AnimatedSection, staggerContainer, fadeUpItem } from "@/components/AnimatedPage";
import { CarCard } from "@/components/CarCard";
import { getFeaturedCars, MOCK_CARS } from "@/lib/mock-data";

const STATS = [
  { label: "Premium Cars", value: "50+" },
  { label: "Happy Renters", value: "2,400+" },
  { label: "Cities Covered", value: "12" },
  { label: "5-Star Reviews", value: "98%" },
];

const HOW_IT_WORKS = [
  {
    icon: Car,
    title: "Choose Your Car",
    description: "Browse our curated fleet of premium vehicles. Filter by category, price, and availability.",
    color: "text-brand-400",
    bg: "bg-brand-500/10",
  },
  {
    icon: Clock,
    title: "Book in Minutes",
    description: "Select your dates, pick-up location, and complete your booking in under 3 minutes.",
    color: "text-blue-400",
    bg: "bg-blue-500/10",
  },
  {
    icon: MapPin,
    title: "Pick Up & Drive",
    description: "Show your confirmation, grab the keys, and hit the road. It's that simple.",
    color: "text-green-400",
    bg: "bg-green-500/10",
  },
];

const TESTIMONIALS = [
  {
    name: "Sarah Johnson",
    role: "Business Traveler",
    comment: "Rented the Tesla Model S for a week. Flawless experience from booking to drop-off. Will definitely use again!",
    rating: 5,
    car: "Tesla Model S Plaid",
    avatar: "SJ",
  },
  {
    name: "Marcus Chen",
    role: "Automotive Enthusiast",
    comment: "The Porsche 911 was an absolute dream. DriveElite's service is impeccable and the car was immaculate.",
    rating: 5,
    car: "Porsche 911 Carrera S",
    avatar: "MC",
  },
  {
    name: "Priya Patel",
    role: "Weekend Explorer",
    comment: "Took the Range Rover on a mountain trip. It handled everything perfectly. Best car rental experience ever.",
    rating: 5,
    car: "Range Rover Sport SVR",
    avatar: "PP",
  },
];

export default function HomePage() {
  const featuredCars = getFeaturedCars();

  return (
    <div className="overflow-hidden">
      {/* ── Hero ─────────────────────────────────────────────────────────── */}
      <section className="relative min-h-screen flex items-center justify-center pt-16">
        {/* Background */}
        <div className="absolute inset-0 hero-gradient" />
        <div className="absolute inset-0">
          <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-500/5 rounded-full blur-3xl animate-float" />
          <div className="absolute bottom-1/4 right-1/4 w-80 h-80 bg-brand-600/5 rounded-full blur-3xl animate-float" style={{ animationDelay: "3s" }} />
        </div>

        {/* Grid overlay */}
        <div
          className="absolute inset-0 opacity-[0.03]"
          style={{
            backgroundImage:
              "linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px)",
            backgroundSize: "60px 60px",
          }}
        />

        <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          {/* Tag */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="inline-flex items-center gap-2 px-4 py-2 bg-brand-500/10 border border-brand-500/20 rounded-full text-brand-400 text-sm font-medium mb-6"
          >
            <Zap className="w-4 h-4" />
            Premium Car Rentals · Book in Under 3 Minutes
          </motion.div>

          {/* Headline */}
          <motion.h1
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.1 }}
            className="font-display text-5xl md:text-7xl lg:text-8xl font-bold text-white leading-[1.05] mb-6"
          >
            Drive the Car
            <br />
            <span className="gradient-text">of Your Dreams</span>
          </motion.h1>

          {/* Subheadline */}
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="text-dark-300 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed"
          >
            From sleek electrics to raw supercars — our curated fleet delivers unforgettable
            drives with transparent pricing and zero hassle.
          </motion.p>

          {/* CTAs */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.3 }}
            className="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16"
          >
            <Link href="/cars" className="btn-primary text-lg px-8 py-4 shadow-2xl shadow-brand-500/30">
              Browse Fleet
              <ArrowRight className="w-5 h-5" />
            </Link>
            <Link href="#how-it-works" className="btn-secondary text-lg px-8 py-4">
              How It Works
            </Link>
          </motion.div>

          {/* Stats */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto"
          >
            {STATS.map((stat) => (
              <div key={stat.label} className="glass rounded-2xl p-4">
                <p className="font-display text-2xl md:text-3xl font-bold text-white">{stat.value}</p>
                <p className="text-dark-400 text-sm mt-1">{stat.label}</p>
              </div>
            ))}
          </motion.div>

          {/* Scroll hint */}
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 1.2 }}
            className="absolute bottom-8 left-1/2 -translate-x-1/2"
          >
            <motion.div
              animate={{ y: [0, 8, 0] }}
              transition={{ repeat: Infinity, duration: 1.5, ease: "easeInOut" }}
              className="flex flex-col items-center gap-2 text-dark-500"
            >
              <span className="text-xs">Scroll to explore</span>
              <ChevronDown className="w-5 h-5" />
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* ── Featured Cars ─────────────────────────────────────────────────── */}
      <section className="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <AnimatedSection className="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
          <div>
            <p className="text-brand-400 text-sm font-semibold uppercase tracking-widest mb-2">
              Our Fleet
            </p>
            <h2 className="section-title text-white">
              Featured <span className="gradient-text">Vehicles</span>
            </h2>
          </div>
          <Link href="/cars" className="btn-outline shrink-0">
            View All Cars <ArrowRight className="w-4 h-4" />
          </Link>
        </AnimatedSection>

        <motion.div
          variants={staggerContainer}
          initial="initial"
          whileInView="animate"
          viewport={{ once: true, margin: "-60px" }}
          className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
        >
          {featuredCars.slice(0, 6).map((car, i) => (
            <CarCard key={car.id} car={car} index={i} />
          ))}
        </motion.div>
      </section>

      {/* ── How It Works ──────────────────────────────────────────────────── */}
      <section id="how-it-works" className="py-20 bg-dark-900/50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <AnimatedSection className="text-center mb-14">
            <p className="text-brand-400 text-sm font-semibold uppercase tracking-widest mb-2">
              Simple Process
            </p>
            <h2 className="section-title text-white">
              Book in <span className="gradient-text">3 Easy Steps</span>
            </h2>
          </AnimatedSection>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {HOW_IT_WORKS.map((step, i) => (
              <AnimatedSection key={step.title} delay={i * 0.12}>
                <div className="relative p-8 card hover:border-dark-600 transition-colors">
                  <div className={`w-14 h-14 ${step.bg} rounded-2xl flex items-center justify-center mb-5`}>
                    <step.icon className={`w-7 h-7 ${step.color}`} />
                  </div>
                  <div className="absolute top-6 right-6 text-6xl font-display font-bold text-dark-800 select-none">
                    {String(i + 1).padStart(2, "0")}
                  </div>
                  <h3 className="font-display text-xl font-bold text-white mb-3">{step.title}</h3>
                  <p className="text-dark-400 leading-relaxed">{step.description}</p>
                </div>
              </AnimatedSection>
            ))}
          </div>
        </div>
      </section>

      {/* ── Trust Banner ─────────────────────────────────────────────────── */}
      <section className="py-14 border-y border-dark-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            {[
              { icon: Shield, title: "Fully Insured", desc: "All vehicles come with comprehensive coverage" },
              { icon: Clock, title: "24/7 Support", desc: "Our team is always here when you need help" },
              { icon: Star, title: "Top Rated", desc: "4.9 average rating from over 2,400+ renters" },
            ].map((item, i) => (
              <AnimatedSection key={item.title} delay={i * 0.1} className="flex flex-col items-center gap-3">
                <div className="w-12 h-12 bg-brand-500/10 border border-brand-500/20 rounded-xl flex items-center justify-center">
                  <item.icon className="w-6 h-6 text-brand-400" />
                </div>
                <h4 className="font-semibold text-white">{item.title}</h4>
                <p className="text-dark-400 text-sm">{item.desc}</p>
              </AnimatedSection>
            ))}
          </div>
        </div>
      </section>

      {/* ── Testimonials ──────────────────────────────────────────────────── */}
      <section className="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <AnimatedSection className="text-center mb-14">
          <p className="text-brand-400 text-sm font-semibold uppercase tracking-widest mb-2">
            Reviews
          </p>
          <h2 className="section-title text-white">
            What Drivers <span className="gradient-text">Say</span>
          </h2>
        </AnimatedSection>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {TESTIMONIALS.map((t, i) => (
            <AnimatedSection key={t.name} delay={i * 0.1}>
              <div className="card p-6 h-full flex flex-col">
                <div className="flex gap-1 mb-4">
                  {Array.from({ length: t.rating }).map((_, j) => (
                    <Star key={j} className="w-4 h-4 text-yellow-400 fill-yellow-400" />
                  ))}
                </div>
                <p className="text-dark-300 leading-relaxed flex-1 mb-6">&ldquo;{t.comment}&rdquo;</p>
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-brand-500/20 border border-brand-500/30 flex items-center justify-center text-brand-400 font-bold text-sm">
                    {t.avatar}
                  </div>
                  <div>
                    <p className="font-semibold text-white text-sm">{t.name}</p>
                    <p className="text-dark-500 text-xs">{t.role} · {t.car}</p>
                  </div>
                </div>
              </div>
            </AnimatedSection>
          ))}
        </div>
      </section>

      {/* ── CTA Banner ────────────────────────────────────────────────────── */}
      <section className="py-20 mx-4 sm:mx-6 lg:mx-8 mb-4">
        <AnimatedSection>
          <div className="relative overflow-hidden max-w-7xl mx-auto rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 p-12 md:p-16 text-center">
            <div className="absolute inset-0 opacity-10">
              <div className="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-1/2 -translate-y-1/2 blur-2xl" />
              <div className="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2 blur-3xl" />
            </div>
            <div className="relative z-10">
              <h2 className="font-display text-4xl md:text-5xl font-bold text-white mb-4">
                Ready to Hit the Road?
              </h2>
              <p className="text-brand-100 text-lg mb-8 max-w-xl mx-auto">
                Browse our full fleet and find the perfect car for your next adventure.
                Free cancellation on most bookings.
              </p>
              <Link
                href="/cars"
                className="inline-flex items-center gap-2 px-8 py-4 bg-white text-brand-600 font-bold text-lg rounded-xl hover:bg-brand-50 transition-colors shadow-2xl"
              >
                Explore Our Fleet
                <ArrowRight className="w-5 h-5" />
              </Link>
            </div>
          </div>
        </AnimatedSection>
      </section>
    </div>
  );
}
