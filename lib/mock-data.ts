// Mock car data — replace with real DB queries once Prisma is connected
export type Car = {
  id: string;
  make: string;
  model: string;
  year: number;
  category: string;
  description: string;
  pricePerDay: number;
  images: string[];
  seats: number;
  doors: number;
  transmission: "Automatic" | "Manual";
  fuelType: "Petrol" | "Diesel" | "Electric" | "Hybrid";
  mileage: number;
  color: string;
  isAvailable: boolean;
  isFeatured: boolean;
  location: string;
  features: string[];
  rating: number;
  reviewCount: number;
};

export const MOCK_CARS: Car[] = [
  {
    id: "car_001",
    make: "BMW",
    model: "M5 Competition",
    year: 2024,
    category: "LUXURY",
    description:
      "Experience the pinnacle of performance luxury. The BMW M5 Competition delivers an exhilarating 625 hp with a sophisticated AWD system, blending everyday comfort with supercar performance.",
    pricePerDay: 299,
    images: [
      "https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800&q=80",
      "https://images.unsplash.com/photo-1617654112368-307921291f42?w=800&q=80",
      "https://images.unsplash.com/photo-1580274455191-1c62238fa333?w=800&q=80",
    ],
    seats: 5,
    doors: 4,
    transmission: "Automatic",
    fuelType: "Petrol",
    mileage: 10.5,
    color: "Frozen Black",
    isAvailable: true,
    isFeatured: true,
    location: "Downtown",
    features: ["GPS Navigation", "Heated Seats", "Sunroof", "Harman Kardon Sound", "Apple CarPlay", "Lane Assist"],
    rating: 4.9,
    reviewCount: 87,
  },
  {
    id: "car_002",
    make: "Tesla",
    model: "Model S Plaid",
    year: 2024,
    category: "ELECTRIC",
    description:
      "The future of performance is here. Tesla Model S Plaid accelerates 0–60 mph in 1.99 seconds with a tri-motor all-wheel drive system. Cutting-edge autopilot technology included.",
    pricePerDay: 249,
    images: [
      "https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=800&q=80",
      "https://images.unsplash.com/photo-1571987502227-9231b837d92a?w=800&q=80",
      "https://images.unsplash.com/photo-1620891549027-942fdc95d3f5?w=800&q=80",
    ],
    seats: 5,
    doors: 4,
    transmission: "Automatic",
    fuelType: "Electric",
    mileage: 0,
    color: "Pearl White",
    isAvailable: true,
    isFeatured: true,
    location: "Airport",
    features: ["Autopilot", "Full Self-Driving", "17\" Touchscreen", "Over-the-Air Updates", "Supercharger Access"],
    rating: 4.8,
    reviewCount: 124,
  },
  {
    id: "car_003",
    make: "Porsche",
    model: "911 Carrera S",
    year: 2024,
    category: "SPORTS",
    description:
      "An icon reinvented. The Porsche 911 Carrera S is the ultimate sports car experience, combining razor-sharp handling with 450 hp from its rear-mounted flat-six engine.",
    pricePerDay: 349,
    images: [
      "https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80",
      "https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=800&q=80",
      "https://images.unsplash.com/photo-1610769921958-5b7f3e1e5f61?w=800&q=80",
    ],
    seats: 4,
    doors: 2,
    transmission: "Automatic",
    fuelType: "Petrol",
    mileage: 11.8,
    color: "GT Silver",
    isAvailable: false,
    isFeatured: true,
    location: "Downtown",
    features: ["Sport Chrono Package", "BOSE Sound", "Sport Exhaust", "Ceramic Brakes", "LED Matrix Headlights"],
    rating: 5.0,
    reviewCount: 56,
  },
  {
    id: "car_004",
    make: "Range Rover",
    model: "Sport SVR",
    year: 2024,
    category: "SUV",
    description:
      "Luxury meets capability. The Range Rover Sport SVR combines breathtaking off-road ability with sumptuous interior comfort and 575 hp of supercharged V8 performance.",
    pricePerDay: 279,
    images: [
      "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800&q=80",
      "https://images.unsplash.com/photo-1583121274602-3e2820c69888?w=800&q=80",
      "https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800&q=80",
    ],
    seats: 7,
    doors: 5,
    transmission: "Automatic",
    fuelType: "Petrol",
    mileage: 9.2,
    color: "Santorini Black",
    isAvailable: true,
    isFeatured: false,
    location: "North Hub",
    features: ["Air Suspension", "Terrain Response 2", "Meridian Sound", "Panoramic Roof", "4-Zone Climate"],
    rating: 4.7,
    reviewCount: 43,
  },
  {
    id: "car_005",
    make: "Mercedes-Benz",
    model: "AMG GT 63 S",
    year: 2024,
    category: "SPORTS",
    description:
      "Where comfort meets ferocity. The AMG GT 63 S 4-Door Coupe delivers 630 hp, a 3.2-second 0–60 sprint, and a cabin that rivals the S-Class in luxury.",
    pricePerDay: 329,
    images: [
      "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&q=80",
      "https://images.unsplash.com/photo-1553440569-bcc63803a83d?w=800&q=80",
      "https://images.unsplash.com/photo-1609521263047-f8f205293f24?w=800&q=80",
    ],
    seats: 4,
    doors: 4,
    transmission: "Automatic",
    fuelType: "Petrol",
    mileage: 10.1,
    color: "Obsidian Black",
    isAvailable: true,
    isFeatured: false,
    location: "Airport",
    features: ["AMG TRACK PACE", "Burmester 3D Sound", "Active Rear-Axle Steering", "Night Package", "Carbon Fiber Trim"],
    rating: 4.8,
    reviewCount: 72,
  },
  {
    id: "car_006",
    make: "Audi",
    model: "e-tron GT",
    year: 2024,
    category: "ELECTRIC",
    description:
      "Elegance electrified. The Audi e-tron GT RS combines sleek Gran Turismo styling with 637 hp and 800V charging architecture for ultra-fast charging.",
    pricePerDay: 259,
    images: [
      "https://images.unsplash.com/photo-1614162692292-7ac56d7f7f1e?w=800&q=80",
      "https://images.unsplash.com/photo-1617654112368-307921291f42?w=800&q=80",
      "https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800&q=80",
    ],
    seats: 4,
    doors: 4,
    transmission: "Automatic",
    fuelType: "Electric",
    mileage: 0,
    color: "Kemora Grey",
    isAvailable: true,
    isFeatured: true,
    location: "South Hub",
    features: ["Matrix LED", "Bang & Olufsen Sound", "Head-Up Display", "Virtual Mirrors", "800V Fast Charging"],
    rating: 4.7,
    reviewCount: 38,
  },
  {
    id: "car_007",
    make: "Toyota",
    model: "Camry XSE",
    year: 2023,
    category: "SEDAN",
    description:
      "The sweet spot between performance and practicality. The Toyota Camry XSE V6 offers 301 hp, sporty styling, and legendary Toyota reliability — perfect for any occasion.",
    pricePerDay: 89,
    images: [
      "https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800&q=80",
      "https://images.unsplash.com/photo-1590362891991-f776e747a588?w=800&q=80",
      "https://images.unsplash.com/photo-1536700503339-1e4b06520771?w=800&q=80",
    ],
    seats: 5,
    doors: 4,
    transmission: "Automatic",
    fuelType: "Petrol",
    mileage: 14.7,
    color: "Midnight Black",
    isAvailable: true,
    isFeatured: false,
    location: "Downtown",
    features: ["Apple CarPlay", "Android Auto", "Toyota Safety Sense", "Wireless Charging", "8\" Touchscreen"],
    rating: 4.5,
    reviewCount: 210,
  },
  {
    id: "car_008",
    make: "Lamborghini",
    model: "Huracán EVO",
    year: 2024,
    category: "SPORTS",
    description:
      "Pure, visceral performance. The Lamborghini Huracán EVO packs a naturally aspirated 5.2L V10 producing 640 hp. Every second behind the wheel is an unforgettable experience.",
    pricePerDay: 799,
    images: [
      "https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=800&q=80",
      "https://images.unsplash.com/photo-1566473965997-3de9c817e938?w=800&q=80",
      "https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80",
    ],
    seats: 2,
    doors: 2,
    transmission: "Automatic",
    fuelType: "Petrol",
    mileage: 8.5,
    color: "Arancio Borealis",
    isAvailable: true,
    isFeatured: true,
    location: "VIP Lounge",
    features: ["Launch Control", "LDVI AI System", "Titanium Exhaust", "Carbon Ceramic Brakes", "Sport Seats"],
    rating: 5.0,
    reviewCount: 29,
  },
];

export function getCarById(id: string): Car | undefined {
  return MOCK_CARS.find((car) => car.id === id);
}

export function getFeaturedCars(): Car[] {
  return MOCK_CARS.filter((car) => car.isFeatured);
}

export function getAvailableCars(): Car[] {
  return MOCK_CARS.filter((car) => car.isAvailable);
}

export function filterCars(opts: {
  category?: string;
  minPrice?: number;
  maxPrice?: number;
  search?: string;
  transmission?: string;
  available?: boolean;
}): Car[] {
  return MOCK_CARS.filter((car) => {
    if (opts.category && opts.category !== "ALL" && car.category !== opts.category) return false;
    if (opts.minPrice !== undefined && car.pricePerDay < opts.minPrice) return false;
    if (opts.maxPrice !== undefined && car.pricePerDay > opts.maxPrice) return false;
    if (opts.available !== undefined && car.isAvailable !== opts.available) return false;
    if (opts.transmission && car.transmission.toUpperCase() !== opts.transmission.toUpperCase()) return false;
    if (opts.search) {
      const q = opts.search.toLowerCase();
      if (
        !car.make.toLowerCase().includes(q) &&
        !car.model.toLowerCase().includes(q) &&
        !car.category.toLowerCase().includes(q)
      )
        return false;
    }
    return true;
  });
}
