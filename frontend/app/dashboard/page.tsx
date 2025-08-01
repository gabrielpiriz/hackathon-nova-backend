"use client"

import { useEffect, useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line } from "recharts"
import { TrendingUp, Package, DollarSign, BarChart3, Beef } from "lucide-react"

interface DashboardStats {
  totalBatches: number
  totalAnimals: number
  averagePriceARS: number
  averagePriceUSD: number
  monthlyVariation: number
  recentSales: any[]
  batchesByType: any[]
  priceHistory: any[]
}

export default function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchDashboardData()
  }, [])

  const fetchDashboardData = async () => {
    try {
      const token = localStorage.getItem("token")

      // Fetch batches
      const batchesResponse = await fetch("http://127.0.0.1:8000/api/batches", {
        headers: { Authorization: `Bearer ${token}` },
      })
      const batches = await batchesResponse.json()

      // Fetch animal types
      const typesResponse = await fetch("http://127.0.0.1:8000/api/animal-types", {
        headers: { Authorization: `Bearer ${token}` },
      })
      const animalTypes = await typesResponse.json()

      // Process data for dashboard
      const totalBatches = batches.length
      const totalAnimals = batches.reduce((sum: number, batch: any) => sum + batch.quantity, 0)
      const averagePriceARS =
        batches.reduce((sum: number, batch: any) => sum + batch.suggested_price_ars, 0) / batches.length || 0
      const averagePriceUSD =
        batches.reduce((sum: number, batch: any) => sum + batch.suggested_price_usd, 0) / batches.length || 0

      // Group batches by animal type
      const batchesByType = animalTypes.map((type: any) => ({
        name: type.name,
        quantity: batches
          .filter((batch: any) => batch.animal_type_id === type.id)
          .reduce((sum: number, batch: any) => sum + batch.quantity, 0),
      }))

      // Mock price history data
      const priceHistory = [
        { month: "Ene", ars: 45000, usd: 180 },
        { month: "Feb", ars: 47000, usd: 185 },
        { month: "Mar", ars: 46500, usd: 182 },
        { month: "Abr", ars: 48000, usd: 190 },
        { month: "May", ars: 49500, usd: 195 },
        { month: "Jun", ars: 51000, usd: 200 },
      ]

      setStats({
        totalBatches,
        totalAnimals,
        averagePriceARS,
        averagePriceUSD,
        monthlyVariation: 5.2, // Mock data
        recentSales: [], // Would come from sales API
        batchesByType,
        priceHistory,
      })
    } catch (error) {
      console.error("Error fetching dashboard data:", error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="p-6">
        <div className="animate-pulse space-y-6">
          <div className="h-8 bg-gray-200 rounded w-1/4"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {[...Array(4)].map((_, i) => (
              <div key={i} className="h-32 bg-gray-200 rounded"></div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Resumen de tu operación ganadera</p>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Lotes</CardTitle>
            <Package className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats?.totalBatches || 0}</div>
            <p className="text-xs text-muted-foreground">{stats?.totalAnimals || 0} animales en total</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Precio Promedio ARS</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${stats?.averagePriceARS.toLocaleString() || 0}</div>
            <div className="flex items-center text-xs text-green-600">
              <TrendingUp className="h-3 w-3 mr-1" />+{stats?.monthlyVariation || 0}% este mes
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Precio Promedio USD</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">${stats?.averagePriceUSD.toLocaleString() || 0}</div>
            <div className="flex items-center text-xs text-green-600">
              <TrendingUp className="h-3 w-3 mr-1" />+{stats?.monthlyVariation || 0}% este mes
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Variación Mensual</CardTitle>
            <BarChart3 className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">+{stats?.monthlyVariation || 0}%</div>
            <Badge variant="secondary" className="text-green-600">
              Tendencia positiva
            </Badge>
          </CardContent>
        </Card>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Stock por Tipo de Animal</CardTitle>
            <CardDescription>Distribución actual de animales</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={stats?.batchesByType || []}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="quantity" fill="#16a34a" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Evolución de Precios</CardTitle>
            <CardDescription>Tendencia de precios últimos 6 meses</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={stats?.priceHistory || []}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Line type="monotone" dataKey="ars" stroke="#16a34a" name="ARS" />
                <Line type="monotone" dataKey="usd" stroke="#2563eb" name="USD" />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* AI Suggestions */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center">
            <Beef className="mr-2 h-5 w-5" />
            Sugerencias de IA
          </CardTitle>
          <CardDescription>Análisis inteligente de precios basado en datos históricos</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="p-4 bg-green-50 rounded-lg border border-green-200">
              <h4 className="font-medium text-green-800">Oportunidad de Venta</h4>
              <p className="text-sm text-green-700 mt-1">
                Los precios del ganado bovino están 8% por encima del promedio histórico. Es un buen momento para vender
                lotes de más de 24 meses.
              </p>
            </div>
            <div className="p-4 bg-blue-50 rounded-lg border border-blue-200">
              <h4 className="font-medium text-blue-800">Tendencia del Mercado</h4>
              <p className="text-sm text-blue-700 mt-1">
                Se proyecta un aumento del 3-5% en los precios para el próximo trimestre debido a la demanda estacional.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
