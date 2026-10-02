/*
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package org.apache.catalina.core;

/**
 * Holds APR status without the need to load other classes.
 *
 * @deprecated Unused. Use {@link org.apache.tomcat.jni.AprStatus} instead. This class will be removed in Tomcat 12
 *                 onwards.
 */
@Deprecated
public class AprStatus {

    public static boolean isAprInitialized() {
        return org.apache.tomcat.jni.AprStatus.isAprInitialized();
    }

    public static boolean isAprAvailable() {
        return org.apache.tomcat.jni.AprStatus.isAprAvailable();
    }

    public static boolean getUseAprConnector() {
        return org.apache.tomcat.jni.AprStatus.getUseAprConnector();
    }

    public static boolean getUseOpenSSL() {
        return org.apache.tomcat.jni.AprStatus.getUseOpenSSL();
    }

    public static boolean isInstanceCreated() {
        return org.apache.tomcat.jni.AprStatus.isInstanceCreated();
    }

    public static void setAprInitialized(boolean aprInitialized) {
        org.apache.tomcat.jni.AprStatus.setAprInitialized(aprInitialized);
    }

    public static void setAprAvailable(boolean aprAvailable) {
        org.apache.tomcat.jni.AprStatus.setAprAvailable(aprAvailable);
    }

    public static void setUseAprConnector(boolean useAprConnector) {
        org.apache.tomcat.jni.AprStatus.setUseAprConnector(useAprConnector);
    }

    public static void setUseOpenSSL(boolean useOpenSSL) {
        org.apache.tomcat.jni.AprStatus.setUseOpenSSL(useOpenSSL);
    }

    public static void setInstanceCreated(boolean instanceCreated) {
        org.apache.tomcat.jni.AprStatus.setInstanceCreated(instanceCreated);
    }

    /**
     * @return the openSSLVersion
     */
    public static int getOpenSSLVersion() {
        return org.apache.tomcat.jni.AprStatus.getOpenSSLVersion();
    }

    /**
     * @param openSSLVersion the openSSLVersion to set
     */
    public static void setOpenSSLVersion(int openSSLVersion) {
        org.apache.tomcat.jni.AprStatus.setOpenSSLVersion(openSSLVersion);
    }
}
