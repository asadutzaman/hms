package com.cedarview.hms.di

import com.cedarview.hms.BuildConfig
import com.cedarview.hms.core.network.AuthInterceptor
import com.cedarview.hms.data.remote.api.AdminApi
import com.cedarview.hms.data.remote.api.AuthApi
import com.cedarview.hms.data.remote.api.DoctorApi
import com.cedarview.hms.data.remote.api.NurseApi
import com.cedarview.hms.data.remote.api.OnCallApi
import com.cedarview.hms.data.remote.api.PatientApi
import com.cedarview.hms.data.remote.api.WardApi
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import kotlinx.serialization.ExperimentalSerializationApi
import kotlinx.serialization.json.Json
import kotlinx.serialization.json.JsonNamingStrategy
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.kotlinx.serialization.asConverterFactory
import java.util.concurrent.TimeUnit
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    @OptIn(ExperimentalSerializationApi::class)
    @Provides
    @Singleton
    fun provideJson(): Json = Json {
        ignoreUnknownKeys = true       // tolerate the rich API resources
        coerceInputValues = true       // null -> default for non-null fields
        explicitNulls = false
        namingStrategy = JsonNamingStrategy.SnakeCase
    }

    @Provides
    @Singleton
    fun provideOkHttpClient(authInterceptor: AuthInterceptor): OkHttpClient {
        val logging = HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG) HttpLoggingInterceptor.Level.BODY
            else HttpLoggingInterceptor.Level.NONE
        }
        return OkHttpClient.Builder()
            .addInterceptor(authInterceptor)
            .addInterceptor(logging)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .build()
    }

    @Provides
    @Singleton
    fun provideRetrofit(client: OkHttpClient, json: Json): Retrofit {
        val contentType = "application/json".toMediaType()
        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(client)
            .addConverterFactory(json.asConverterFactory(contentType))
            .build()
    }

    @Provides
    @Singleton
    fun provideAuthApi(retrofit: Retrofit): AuthApi = retrofit.create(AuthApi::class.java)

    @Provides
    @Singleton
    fun providePatientApi(retrofit: Retrofit): PatientApi = retrofit.create(PatientApi::class.java)

    @Provides
    @Singleton
    fun provideDoctorApi(retrofit: Retrofit): DoctorApi = retrofit.create(DoctorApi::class.java)

    @Provides
    @Singleton
    fun provideAdminApi(retrofit: Retrofit): AdminApi = retrofit.create(AdminApi::class.java)

    @Provides
    @Singleton
    fun provideWardApi(retrofit: Retrofit): WardApi = retrofit.create(WardApi::class.java)

    @Provides
    @Singleton
    fun provideNurseApi(retrofit: Retrofit): NurseApi = retrofit.create(NurseApi::class.java)

    @Provides
    @Singleton
    fun provideOnCallApi(retrofit: Retrofit): OnCallApi = retrofit.create(OnCallApi::class.java)
}
